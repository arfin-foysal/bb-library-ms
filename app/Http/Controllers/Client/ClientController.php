<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Author;
use App\Models\Item;
use App\Models\ItemAuthor;
use App\Models\ItemRental;
use App\Models\ItemReview;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\TryCatch;

class ClientController extends Controller
{
    use ApiResponseTrait;

    public function getAllBook(Request $request)
    {

        $page = 0;
        $limit = $request->limit;
        $items = Item::leftJoin('categories', 'categories.id', '=', 'items.category_id')
            ->leftJoin('item_reviews', 'item_reviews.item_id', '=', 'items.id')
            ->select(
                'items.id as id',
                'items.title as title',
                'items.photo as photo',
                'items.category_id as category_id',
                'categories.name as category_name',
                'items.updated_at as updated_at',

                DB::raw('AVG(item_reviews.rating) as rating')

            )

            ->groupBy('items.id',
                'items.title',
                'items.photo',
                'items.category_id',
                'categories.name',
                'items.updated_at'
            )

            ->orderBy('updated_at', 'desc')->paginate($limit, ['*'], 'page', $page);

        foreach ($items as $item) {
            $item->authors = ItemAuthor::where('item_id', $item->id)
                ->leftJoin('authors', 'authors.id', '=', 'item_authors.author_id')
                ->select(
                    'authors.name as name',
                    'authors.id as id',
                    'authors.photo as author_photo',
                    'authors.bio as author_bio'

                )
                ->get();
        }

        return $this->apiResponse($items, 'all Book item', true, 200);
    }
    public function getHomePageBook(Request $request)
    {

        $items = Item::select(
            'items.*',
            'categories.name as category_name',
        )
            ->leftJoin('categories', 'categories.id', '=', 'items.category_id')
            ->latest()->limit(10)->get();

        foreach ($items as $item) {
            $item->authors = ItemAuthor::where('item_id', $item->id)
                ->leftJoin('authors', 'authors.id', '=', 'item_authors.author_id')
                ->select(
                    'authors.name as name',
                    'authors.id as id',
                    'authors.photo as author_photo',
                    'authors.bio as author_bio'

                )
                ->get();
        }

        return $this->apiResponse($items, 'Book item', true, 200);
    }




    public function getItemById($id)
    {

        $items = Item::where('items.id', $id)
            ->leftJoin('categories', 'categories.id', '=', 'items.category_id')
            ->leftJoin('publishers', 'publishers.id', '=', 'items.publisher_id')
            ->leftJoin('countries', 'countries.id', '=', 'items.country_id')
            ->leftJoin('languages', 'languages.id', '=', 'items.language_id')
            ->leftJoin('item_inventory_stocks', 'item_inventory_stocks.item_id', '=', 'items.id')

            ->select(
                'items.*',
                'categories.name as category_name',
                'publishers.name as publisher_name',
                'countries.name as country_name',
                'languages.name as language_name',
                'item_inventory_stocks.qty as qty',
            )

            ->first();
        $items->authors = ItemAuthor::where('item_id', $items->id)
            ->leftJoin('authors', 'authors.id', '=', 'item_authors.author_id')
            ->select(
                'authors.name as name',
                'authors.id as id',
                'authors.photo as author_photo',
                'authors.bio as author_bio'

            )
            ->get();


        $itemByCategory = Item::where('items.category_id', $items->category_id)
            ->where('items.id', '!=', $items->id)
            ->leftJoin('categories', 'categories.id', '=', 'items.category_id')
            ->select(
                'items.*',
                'categories.name as category_name',

            )->limit(2)->get();

        foreach ($itemByCategory as $item) {
            $item->authors = ItemAuthor::where('item_id', $item->id)
                ->leftJoin('authors', 'authors.id', '=', 'item_authors.author_id')
                ->select(
                    'authors.name as name',
                    'authors.id as id',
                    'authors.photo as author_photo',
                    'authors.bio as author_bio'

                )
                ->get();
        }
        $items->related_items = $itemByCategory;





        return $this->apiResponse($items, 'Book item', true, 200);
    }





    public function authorDetailsAndBook()
    {

        $authors = Author::get();

        $authors->each(function ($author) {
            $author->items = ItemAuthor::where('author_id', $author->id)
                ->leftJoin('items', 'items.id', '=', 'item_authors.item_id')
                ->leftJoin('categories', 'categories.id', '=', 'items.category_id')
                ->select(
                    'items.title as title',
                    'item_authors.item_id as item_id',
                    'items.photo as photo',
                    'categories.name as category_name',
                )
                ->get();
        });

        return $this->apiResponse($authors, 'all author', true, 200);
    }


    public function singleUser()
    {
        try {
            $user = User::where('id', Auth::user()->id)->first();
            return $this->apiResponse($user, 'user', true, 200);
        } catch (\Throwable $th) {
            return $this->apiResponse('', $th->getMessage(), true, 200);
        }
    }

    public function profileUpdate(Request $request)
    {

        try {

            $user = User::where('id', Auth::user()->id)->first();
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'username' => 'min:4|unique:users,username,' . $user->id,

            ]);
            $imageName = "";
            if ($image = $request->file('profile_photo_path')) {
                if ($user->profile_photo_path) {
                    unlink(public_path("images/" . $user->profile_photo_path));
                }

                $imageName = time() . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images'), $imageName);
            } else {
                $imageName = $user->profile_photo_path;
            }

            $user->name = $request->name;
            $user->email = $request->email;
            $user->username = $request->username;
            $user->phone = $request->phone;
            $user->location = $request->location;
            $user->description = $request->description;
            $user->gender = $request->gender;
            $user->profile_photo_path = $imageName;
            $user->save();


            return $this->apiResponse([], 'Profile Update Successfully.', true, 200);
        } catch (\Throwable $th) {
            return $this->apiResponse([], $th->getMessage(), false, 200);
        }
    }


    public function rentItemByUser()
    {

        //rent item get by login user 

        $items = ItemRental::where('user_id', Auth::user()->id)
            ->leftJoin('item_rental_details', 'item_rental_details.item_rental_id', '=', 'item_rentals.id')
            ->leftJoin('items', 'items.id', '=', 'item_rental_details.item_id')
            ->select(
                'items.id as id',
                'items.title as title',
                'items.photo as photo',
                'item_rentals.rental_date as rental_date',


            )->get();

        $items->each(function ($item) {
            $item->authors = ItemAuthor::where('item_id', $item->id)
                ->leftJoin('authors', 'authors.id', '=', 'item_authors.author_id')
                ->select(
                    'authors.name as name',


                )
                ->get();
        });


        return $this->apiResponse($items, 'all Book item', true, 200);
    }

    public function pendingOrderList()
    {
        $items = ItemRental::where('item_rentals.status', 'inactive')
            ->where('item_rentals.user_id', Auth::user()->id)
            ->leftJoin('item_rental_details', 'item_rental_details.item_rental_id', '=', 'item_rentals.id')
            ->leftJoin('items', 'items.id', '=', 'item_rental_details.item_id')
            ->select(
                'items.id as id',
                'items.title as title',
                'items.photo as photo',
                'item_rentals.rental_date as rental_date',


            )->get();

        $items->each(function ($item) {
            $item->authors = ItemAuthor::where('item_authors.item_id', $item->id)
                ->leftJoin('authors', 'authors.id', '=', 'item_authors.author_id')
                ->select(
                    'authors.name as name',
                )
                ->get();
        });

        return $this->apiResponse($items, 'All Pending Book item', true, 200);
    }


    public function ItemReturnTimeExpired()
    {

        $items = ItemRental::where('item_rentals.user_id', Auth::user()->id)

            ->leftJoin('item_rental_details', 'item_rental_details.item_rental_id', '=', 'item_rentals.id')
            ->where([['item_rental_details.return_date', '<', Carbon::now()], ['item_rental_details.status', 'rental']])
            ->leftJoin('items', 'items.id', '=', 'item_rental_details.item_id')
            ->select(
                'items.id as id',
                'items.title as title',
                'items.photo as photo',
                'item_rentals.rental_date as rental_date',
                'item_rental_details.return_date as return_date',
            )
            ->get();
        return $this->apiResponse($items, 'All Pending Book item', true, 200);
    }

    public function virtualItemView($id)
    {
        $item = Item::where('id', $id)->select('id', 'virtual_book')->first();
        return $this->apiResponse($item, 'virtual item', true, 200);
    }



    public function reviewItem(Request $request)
    {
        try {
            $request->validate([
                'item_id' => 'required',
                'rating' => 'required',
                'content' => 'required',
            ]);



            $review = new ItemReview();
            $rating = request('rating');
            $content = request('content');
            $userId = Auth::user()->id;

            if (empty($request->id)) {

                $review->user_id = $userId;
                $review->item_id = $request->item_id;
                $review->rating = $rating;
                $review->content = $content;
                $review->created_by = $userId;
            } else {
                $review = ItemReview::where('id', $request->id)->first();
                $review->rating = $rating;
                $review->content = $content;
                $review->updated_by = $userId;
            }

            $review->save();
            return $this->apiResponse([], 'Review Successfully.', true, 200);
        } catch (\Throwable $th) {
            return $this->apiResponse([], $th->getMessage(), false, 200);
        }
    }

    public function getReviewByUser(Request $request, $id)
    {
        $reviews = ItemReview::where('user_id', Auth::user()->id)
            ->where('item_id', $id)
            ->first();

        return $this->apiResponse($reviews, 'Review Successfully.', true, 200);
    }


    public function getReviewByItem(Request $request, $id)
    {
        $reviews = ItemReview::where('item_id', $id)
            ->leftJoin('users', 'users.id', '=', 'item_reviews.user_id')
            ->select(
                'item_reviews.id as id',
                'item_reviews.rating as rating',
                'item_reviews.content as content',
                'users.name as name',
                'users.profile_photo_path as profile_photo_path',
            )
            ->get();

        return $this->apiResponse($reviews, 'Review Successfully.', true, 200);
    }


    public function commonRatingCalculate()
    {
        // item rating calculate and get

        $items = Item::leftJoin('item_reviews', 'item_reviews.item_id', '=', 'items.id')
            ->select(
                DB::raw('AVG(item_reviews.rating) as rating'),
                'item_reviews.item_id as item_id',
                'items.title as title',

            )



            ->groupBy(
                'item_reviews.item_id',

            )



            ->get();

        return $this->apiResponse($items, 'All Book item', true, 200);
    }
}
