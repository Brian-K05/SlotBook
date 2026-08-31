<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Your SlotBook hour is confirmed</title>
</head>
<body style="margin:0;background:#f3efe7;color:#1c1917;font-family:'Source Sans 3', source-sans-3, Calibri, sans-serif;">
    <div style="max-width:32rem;margin:0 auto;padding:2.5rem 1.25rem;">
        <p style="font-family:Newsreader, Georgia, serif;font-style:italic;font-size:1.75rem;margin:0 0 1rem;">SlotBook</p>
        <p>Hello {{ $booking->guest_name }},</p>
        <p>Your hour is confirmed:</p>
        <p style="font-family:Newsreader, Georgia, serif;font-size:1.35rem;">
            {{ $booking->slot->starts_at->format('l, j F Y') }}<br>
            {{ $booking->slot->starts_at->format('g:i A') }} – {{ $booking->slot->ends_at->format('g:i A') }}
        </p>
        <p>If you need to change it, write back to the person who confirmed you.</p>
        <p style="color:#b45309;margin-top:2rem;">— SlotBook</p>
    </div>
</body>
</html>
