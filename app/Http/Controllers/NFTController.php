<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class postController extends Controller
{
    public function createPost(Request $request){
$incomingFields = $request->validate([
    'title' => 'required',
    'body' => 'required'
]);
$incomingFields['title'] = strip_tags($incomingFields['title']);
$incomingFields['body'] = strip_tags($incomingFields['body']);
$incomingFields['user_id'] = auth()->id();

    Post::create($incomingFields);
return redirect('/test');
    }

    public function showEditScreen(Post $post){
           if(auth()->user()->id !== $post['user_id']){
            redirect('/test');
        }
        return view('edit-post', ['post' => $post]);
    }

    public function updatePost(Post $post, Request $request){
        if(auth()->user()->id !== $post['user_id']){
            return redirect('/test');
        }
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);

        $post->update($incomingFields);
        return redirect('/test');
    }

    public function deletePost(Post $post){
        if(auth()->user()->id === $post['id']){
             $post->delete();
        }
        return redirect('/test');
    }
}