{{-- The items under one roadmap or item, in order, each with its own items (recursive). --}}
<ul class="crm-tree">
    @foreach($tree[$parent] ?? [] as $r)
        <li>
            @include('admin.career_roadmap_sub_modules._tree_row', ['r' => $r, 'top' => false])
            @if(isset($tree[$r->id]))
                @include('admin.career_roadmap_sub_modules._tree', ['parent' => $r->id])
            @endif
        </li>
    @endforeach
</ul>
