<!doctype html>
<html><body style="font-family:Arial,Helvetica,sans-serif;color:#1b2230;line-height:1.5;max-width:560px">
<h2 style="margin:0 0 12px">{{ $alert->title }}</h2>
@if($alert->body)<p>{!! nl2br(e($alert->body)) !!}</p>@endif
<p>
    @if($alert->link_url)<a href="{{ $alert->link_url }}" style="display:inline-block;background:#2c4f8f;color:#fff;padding:10px 18px;border-radius:6px;text-decoration:none">See the details</a>@endif
</p>
@if($alert->source_url)<p style="font-size:13px">Official source: <a href="{{ $alert->source_url }}">{{ parse_url($alert->source_url, PHP_URL_HOST) }}</a></p>@endif
<hr style="border:none;border-top:1px solid #dfe3ea;margin:20px 0">
<p style="color:#5a6475;font-size:12px">You get this because you follow this on Enrollzy.
    <a href="{{ $manageUrl }}">Manage alerts</a> · <a href="{{ $unsubscribeUrl }}">Unsubscribe from email</a></p>
</body></html>
