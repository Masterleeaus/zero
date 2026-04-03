<p>Hello{{ $request->recipient_name ? ' '.$request->recipient_name : '' }},</p>

<p>You have a document request:</p>

<p><strong>{{ $request->title }}</strong></p>

@if($request->message)
<p>{{ $request->message }}</p>
@endif

<p>Upload your file here:</p>
<p><a href="{{ $uploadUrl }}">{{ $uploadUrl }}</a></p>

@if($request->due_at)
<p>Due: {{ $request->due_at->format('Y-m-d') }}</p>
@endif

<p>Thank you.</p>
