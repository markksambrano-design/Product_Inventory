@extends('layouts.app')
@section('title','Login Security')
@section('content')
<div class="mb-4"><h2>Login Security</h2><p class="text-muted">Successful and failed access attempts.</p></div><div class="card shadow-sm"><div class="card-body table-responsive"><table class="table align-middle"><thead><tr><th>Date</th><th>User / Email</th><th>Status</th><th>IP Address</th><th>Device</th></tr></thead><tbody>@foreach($histories as $history)<tr><td>{{ $history->created_at->format('M d, Y h:i A') }}</td><td>{{ $history->user?->name ?? $history->email }}</td><td><span class="badge {{ $history->successful?'bg-success':'bg-danger' }}">{{ $history->successful?'Successful':'Failed' }}</span></td><td>{{ $history->ip_address }}</td><td class="text-truncate" style="max-width:320px">{{ $history->user_agent }}</td></tr>@endforeach</tbody></table>{{ $histories->links() }}</div></div>
@endsection
