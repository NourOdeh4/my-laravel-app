<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function addFavorite(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        Favorite::firstOrCreate([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id
        ]);

        return response()->json(['message' => 'تمت الإضافة للمفضلة']);
    }

    public function getFavorites()
    {
        $favorites = Favorite::with('product')
                             ->where('user_id', auth()->id())
                             ->get();

        return response()->json(['data' => $favorites]);
    }

    public function removeFavorite($id)
    {
        $fav = Favorite::where('user_id', auth()->id())->findOrFail($id);
        $fav->delete();

        return response()->json(['message' => 'تم الحذف من المفضلة']);
    }
}

