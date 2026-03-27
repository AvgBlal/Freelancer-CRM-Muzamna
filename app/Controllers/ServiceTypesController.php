<?php
/**
 * Service Types Controller
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\ServiceTypeRepo;

class ServiceTypesController
{
    public function index(): void
    {
        Auth::requireAuth();

        $types = ServiceTypeRepo::getAll();

        foreach ($types as &$type) {
            $type['service_count'] = ServiceTypeRepo::getServiceCount($type['id']);
        }
        unset($type);

        $csrf = CSRF::field();
        require __DIR__ . '/../Views/service-types/index.php';
    }

    public function store(): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $validator = new Validator($_POST);
        $validator->required('slug', __('service_types.code'))->max('slug', 50);
        $validator->required('label', __('service_types.name_ar'))->max('label', 100);

        if ($validator->fails()) {
            Response::withError($validator->firstError());
            Response::redirect('/service-types');
            return;
        }

        $slug = preg_replace('/[^a-z0-9_-]/', '', strtolower(trim($_POST['slug'])));
        if (empty($slug)) {
            Response::withError(__('service_types.code_error'));
            Response::redirect('/service-types');
            return;
        }

        ServiceTypeRepo::create($slug, trim($_POST['label']));

        Response::withSuccess(__('service_types.created'));
        Response::redirect('/service-types');
    }

    public function update(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $type = ServiceTypeRepo::find($id);
        if (!$type) {
            http_response_code(404);
            echo '404 - Type not found';
            return;
        }

        $validator = new Validator($_POST);
        $validator->required('slug', __('service_types.code'))->max('slug', 50);
        $validator->required('label', __('service_types.name_ar'))->max('label', 100);

        if ($validator->fails()) {
            Response::withError($validator->firstError());
            Response::redirect('/service-types');
            return;
        }

        $slug = preg_replace('/[^a-z0-9_-]/', '', strtolower(trim($_POST['slug'])));
        ServiceTypeRepo::update($id, $slug, trim($_POST['label']));

        Response::withSuccess(__('service_types.updated'));
        Response::redirect('/service-types');
    }

    public function delete(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $type = ServiceTypeRepo::find($id);
        if (!$type) {
            http_response_code(404);
            echo '404 - Type not found';
            return;
        }

        ServiceTypeRepo::delete($id);

        Response::withSuccess(__('service_types.deleted'));
        Response::redirect('/service-types');
    }
}
