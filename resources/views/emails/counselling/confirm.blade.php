<!doctype html>
<html><body style="font-family:Arial,Helvetica,sans-serif;color:#1b2230;line-height:1.5;max-width:560px">
<h2 style="margin:0 0 12px">Confirm your alerts</h2>
<p>Someone asked to get alerts about <strong>{{ $targetName }}</strong> at this email address. If that was you, confirm below. If not, ignore this email: nothing will be sent.</p>
<p><a href="{{ $confirmUrl }}" style="display:inline-block;background:#2c4f8f;color:#fff;padding:10px 18px;border-radius:6px;text-decoration:none">Confirm my email</a></p>
<p style="color:#5a6475;font-size:13px">The link works for 3 days. <a href="{{ $unsubscribeUrl }}">Do not send me anything</a>.</p>
</body></html>
