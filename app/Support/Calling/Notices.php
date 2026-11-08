<?php

namespace App\Support\Calling;

use App\Models\Admin;
use App\Models\Notice;
use Illuminate\Support\Collection;

/**
 * Notice Board rules. A notice goes to the whole calling team (all), a team (a team lead and everyone under them)
 * or one person. Admins post to anyone; a team lead posts to their own team or one of its members; telecallers read
 * and acknowledge. Admins see every notice; others see what they posted or received.
 */
class Notices
{
    public function __construct(private CommandCenter $cc)
    {
    }

    /** the staff ids a notice is for (never the poster) */
    public function recipients(Notice $n): array
    {
        $ids = match ($n->audience) {
            'team' => array_merge([(int) $n->audience_id], array_intersect($this->cc->below((int) $n->audience_id), $this->cc->callers()->keys()->all())),
            'user' => [(int) $n->audience_id],
            default => $this->cc->callers()->keys()->all(),
        };
        return array_values(array_diff(array_unique($ids), [(int) $n->posted_by]));
    }

    public function canPost(Admin $u): bool
    {
        return in_array($this->cc->role($u), ['admin', 'teamlead'], true);
    }

    /** who a person may post to: [value => label] with values all | team:{id} | user:{id} */
    public function audiences(Admin $u): array
    {
        $role = $this->cc->role($u);
        if (!in_array($role, ['admin', 'teamlead'], true)) return [];
        $out = [];
        if ($role === 'admin') {
            $out['all'] = 'Everyone in the calling team';
            foreach ($this->cc->callers()->filter(fn ($a) => $this->cc->role($a) === 'teamlead') as $tl) $out['team:' . $tl->id] = $tl->name . "'s team";
            foreach ($this->cc->callers() as $a) $out['user:' . $a->id] = $a->name;
        } else {
            $out['team:' . $u->id] = 'My team';
            foreach (array_diff($this->cc->scope($u), [$u->id]) as $id) $out['user:' . $id] = $this->cc->person($id)['name'];
        }
        return $out;
    }

    public function canSee(Admin $u, Notice $n): bool
    {
        return $this->cc->role($u) === 'admin' || (int) $n->posted_by === $u->id || in_array($u->id, $this->recipients($n), true);
    }

    public function canManage(Admin $u, Notice $n): bool
    {
        return $this->cc->role($u) === 'admin' || (int) $n->posted_by === $u->id;
    }

    /** the notices a person sees, pinned first then newest; live only unless $all */
    public function visible(Admin $u, bool $all = false, ?int $limit = null): Collection
    {
        $list = Notice::with('poster:id,name', 'acks')->latest('id')->limit(300)->get()
            ->filter(fn ($n) => $this->canSee($u, $n) && ($all || $n->isLive()))
            ->sortBy([['pinned', 'desc'], ['id', 'desc']])->values();
        return $limit ? $list->take($limit) : $list;
    }

    /** live notices this person still has to acknowledge */
    public function unacked(Admin $u): Collection
    {
        return Notice::with('poster:id,name', 'acks')->where('require_ack', true)->latest('id')->limit(100)->get()
            ->filter(fn ($n) => $n->isLive() && in_array($u->id, $this->recipients($n), true) && !$n->acks->contains('staff_id', $u->id))->values();
    }

    public function audienceLabel(Notice $n): string
    {
        return match ($n->audience) {
            'team' => (int) $n->audience_id === (int) $n->posted_by ? 'My team' : $this->cc->person((int) $n->audience_id)['name'] . "'s team",
            'user' => $this->cc->person((int) $n->audience_id)['name'],
            default => 'Everyone',
        };
    }
}
