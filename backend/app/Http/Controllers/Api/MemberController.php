<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Http\Resources\MemberResource;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $members = Member::where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get();

        return MemberResource::collection($members);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:father,mother,child',
            'avatar' => 'nullable|string',
        ]);

        $member = Member::create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        return new MemberResource($member);
    }

    public function show(Request $request, Member $member)
    {
        $this->authorize($request, $member);
        return new MemberResource($member);
    }

    public function update(Request $request, Member $member)
    {
        $this->authorize($request, $member);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'role' => 'sometimes|in:father,mother,child',
            'avatar' => 'nullable|string',
        ]);

        $member->update($data);

        return new MemberResource($member);
    }

    public function destroy(Request $request, Member $member)
    {
        $this->authorize($request, $member);
        $member->delete();

        return response()->json(['message' => 'Member deleted successfully']);
    }

    public function toggleActive(Request $request, Member $member)
    {
        $this->authorize($request, $member);
        $member->update(['is_active' => !$member->is_active]);

        return new MemberResource($member);
    }

    private function authorize(Request $request, Member $member): void
    {
        abort_if($member->user_id !== $request->user()->id, 403);
    }
}
