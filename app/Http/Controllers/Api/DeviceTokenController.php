// DeviceTokenController.php
public function store(Request $request)
{
$request->validate([
'token' => 'required|string',
'platform' => 'required|in:android,ios',
]);

// Upsert — update if exists, create if not
$request->user()->deviceTokens()->updateOrCreate(
['token' => $request->token],
['platform' => $request->platform],
);

return response()->json(['message' => 'Device token registered']);
}