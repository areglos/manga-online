<?php
namespace App\Repositories;

use App\Repositories\BaseRepository;
use App\Models\Manga;
use App\Models\Category;
use App\Models\CategoryManga;
use App\Models\AuthorManga;
use App\Models\Chapter;
use App\Models\Comment;

class MangaRepository extends BaseRepository
{

    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return Manga::class;
    }
    public function store($request){
        if ($request->hasFile('image')){     
            $path = $request->file('image')->store('public/images');
            $data['image'] = strstr( $path, '/' );
        }
        $data['name'] = $request->name;
        $data['rate'] = 0;
        $data['total_rate'] = 0;
        $data['slug'] = str_slug($request->slug) . time();
        $data['description'] = $request->description;
        $data['status'] = 1;
        $manga = Manga::create($data);
        foreach ($request->category as $key => $category) {
            $cate['category_id'] = $category;
            $cate['manga_id'] = $manga->id;
            CategoryManga::create($cate);
        }
        $author['author_id'] = $request->author;
        $author['manga_id'] = $manga->id;
        AuthorManga::create($author);


        return $manga;
    }

    public function updateStatus($id)
    {
        $result = $this->_model->findOrFail($id);
        $result->status = 1 - $result->status;
        $result->save();

        return $result->status;
    }

    public function updateManga($request)
    {
        $result = $this->find($request->id);
        if ($request->hasFile('image')){     
            $path = $request->file('image')->store('public/images');
            $result->image = strstr( $path, '/' );
        }
        $result->name = $request->name;
        $result->slug = str_slug($request->slug);
        $result->description = $request->description;
        $result->save();

        return $result;
    }

    public function getLimit()
    {
        $mangas = Manga::where('status', config('assets.is_active'))->orderBy('id', 'desc')->paginate(config('assets.paginate'));

        return $mangas;
    }

    public function getTopView($limit)
    {
        $mangas = Manga::where('status', config('assets.is_active'))->orderBy('view', 'desc')->take($limit)->get();
        
        return $mangas;
    }

    public function getCategory($category)
    {
        $mangas = Category::where('slug', $category)->first()->mangas;
        
        return $mangas;
    }

    public function getManga($slug)
    {
        $manga = Manga::where('slug', $slug)->first();

        return $manga;
    }

    public function getChapter($slug)
    {
        $chapter = Chapter::where('slug', $slug)->first();

        return $chapter;
    }

    public function createComment($data)
    {
        $manga = Manga::findOrFail($data['manga_id']);
        if ($manga == null) {

            return response()->view('errors/404');
        }
        $manga->count_comment = $manga->count_comment + 1;
        $manga->save();
        $data['user_id'] = session('users')->id;
        $data['type'] = 1;
        $result = Comment::create($data);
        $result['username'] = $result->user->fullname;
        $result['avatar'] = $result->user->avatar;

        return $result;
    }
}
