<?php
/**
 * Tags Controller
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\TagRepo;

class TagsController
{
    public function index(): void
    {
        Auth::requireAuth();

        $tags = TagRepo::getAll();

        // Get client count for each tag
        foreach ($tags as &$tag) {
            $tag['client_count'] = TagRepo::getClientCount($tag['id']);
        }
        unset($tag);

        $csrf = CSRF::field();
        require __DIR__ . '/../Views/tags/index.php';
    }

    public function store(): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $validator = new Validator($_POST);
        $validator->required('name', __('tags.name'))->max('name', 100);

        if ($validator->fails()) {
            Response::withError($validator->firstError());
            Response::redirect('/tags');
            return;
        }

        TagRepo::findOrCreate(trim($_POST['name']));

        Response::withSuccess(__('tags.created'));
        Response::redirect('/tags');
    }

    public function update(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $tag = TagRepo::find($id);
        if (!$tag) {
            http_response_code(404);
            echo '404 - Tag not found';
            return;
        }

        $validator = new Validator($_POST);
        $validator->required('name', __('tags.name'))->max('name', 100);

        if ($validator->fails()) {
            Response::withError($validator->firstError());
            Response::redirect('/tags');
            return;
        }

        TagRepo::update($id, trim($_POST['name']));

        Response::withSuccess(__('tags.updated'));
        Response::redirect('/tags');
    }

    public function delete(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $tag = TagRepo::find($id);
        if (!$tag) {
            http_response_code(404);
            echo '404 - Tag not found';
            return;
        }

        TagRepo::delete($id);

        Response::withSuccess(__('tags.deleted'));
        Response::redirect('/tags');
    }
}
