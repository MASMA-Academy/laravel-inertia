<?php

namespace App\Http\Controllers;

use App\Models\DashboardItem;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DashboardItemController extends Controller
{
    public function index(): Response
    {
        $items = auth()->user()->dashboardItems()->orderBy('position')->get();
        
        return Inertia::render('Dashboard', [
            'items' => $items,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:note,task,link,reminder',
            'color' => 'required|string|in:blue,green,red,yellow,purple,orange',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['position'] = auth()->user()->dashboardItems()->count();

        DashboardItem::create($validated);

        return redirect()->back()->with('success', 'Item added successfully!');
    }

    public function update(Request $request, DashboardItem $item): RedirectResponse
    {
        $this->authorize('update', $item);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:note,task,link,reminder',
            'color' => 'required|string|in:blue,green,red,yellow,purple,orange',
            'is_pinned' => 'boolean',
        ]);

        $item->update($validated);

        return redirect()->back()->with('success', 'Item updated successfully!');
    }

    public function destroy(DashboardItem $item): RedirectResponse
    {
        $this->authorize('delete', $item);

        $item->delete();

        return redirect()->back()->with('success', 'Item deleted successfully!');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:dashboard_items,id',
            'items.*.position' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $itemData) {
            DashboardItem::where('id', $itemData['id'])
                ->where('user_id', auth()->id())
                ->update(['position' => $itemData['position']]);
        }

        return redirect()->back()->with('success', 'Items reordered successfully!');
    }
}