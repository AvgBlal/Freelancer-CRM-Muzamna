<?php
/**
 * Notes Controller
 * Personal notes and reminders CRUD
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\NotesRepo;

class NotesController
{
    /**
     * Check employee owns the note, redirect if not
     */
    private function requireNoteAccess(array $note): bool
    {
        if (Auth::isEmployee() && (int) ($note['created_by'] ?? 0) !== Auth::id()) {
            Response::withError(__('notes.unauthorized'));
            Response::redirect('/notes');
            return false;
        }
        return true;
    }

    public function index(): void
    {
        Auth::requireAuth();

        $filters = [
            'search' => $_GET['search'] ?? '',
            'category' => $_GET['category'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'status' => $_GET['status'] ?? '',
        ];

        // Employees only see their own notes
        if (Auth::isEmployee()) {
            $filters['created_by'] = Auth::id();
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;

        $notes = NotesRepo::getAll($filters, $page, $perPage);
        $totalCount = NotesRepo::getCount($filters);
        $totalPages = max(1, ceil($totalCount / $perPage));
        $stats = NotesRepo::getStats(Auth::isEmployee() ? Auth::id() : null);

        require __DIR__ . '/../Views/notes/index.php';
    }

    public function create(): void
    {
        Auth::requireAuth();
        require __DIR__ . '/../Views/notes/create.php';
    }

    public function store(): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $v = new Validator($_POST);
        $v->required('title')->max('title', 255);

        if (!empty($_POST['due_date'])) {
            $v->date('due_date');
        }

        if (!empty($_POST['category'])) {
            $v->in('category', ['general', 'idea', 'reminder', 'financial', 'personal']);
        }

        if (!empty($_POST['priority'])) {
            $v->in('priority', ['low', 'normal', 'high']);
        }

        if ($v->fails()) {
            $_SESSION['flash_error'] = $v->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        $user = Auth::user();
        $_POST['created_by'] = $user['id'] ?? null;

        $id = NotesRepo::create($_POST);
        Response::withSuccess(__('notes.created'));
        Response::redirect('/notes/' . $id);
    }

    public function show(int $id): void
    {
        Auth::requireAuth();

        $note = NotesRepo::find($id);
        if (!$note) {
            Response::withError(__('notes.not_found'));
            Response::redirect('/notes');
            return;
        }

        if (!$this->requireNoteAccess($note)) return;

        require __DIR__ . '/../Views/notes/show.php';
    }

    public function edit(int $id): void
    {
        Auth::requireAuth();

        $note = NotesRepo::find($id);
        if (!$note) {
            Response::withError(__('notes.not_found'));
            Response::redirect('/notes');
            return;
        }

        if (!$this->requireNoteAccess($note)) return;

        require __DIR__ . '/../Views/notes/edit.php';
    }

    public function update(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $note = NotesRepo::find($id);
        if (!$note) {
            Response::withError(__('notes.not_found'));
            Response::redirect('/notes');
            return;
        }

        if (!$this->requireNoteAccess($note)) return;

        $v = new Validator($_POST);
        $v->required('title')->max('title', 255);

        if (!empty($_POST['due_date'])) {
            $v->date('due_date');
        }

        if (!empty($_POST['category'])) {
            $v->in('category', ['general', 'idea', 'reminder', 'financial', 'personal']);
        }

        if (!empty($_POST['priority'])) {
            $v->in('priority', ['low', 'normal', 'high']);
        }

        if ($v->fails()) {
            $_SESSION['flash_error'] = $v->firstError();
            $_SESSION['old'] = $_POST;
            Response::back();
            return;
        }

        NotesRepo::update($id, $_POST);
        Response::withSuccess(__('notes.updated'));
        Response::redirect('/notes/' . $id);
    }

    public function delete(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $note = NotesRepo::find($id);
        if ($note && !$this->requireNoteAccess($note)) return;

        NotesRepo::delete($id);
        Response::withSuccess(__('notes.deleted'));
        Response::redirect('/notes');
    }

    public function togglePin(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $note = NotesRepo::find($id);
        if ($note && !$this->requireNoteAccess($note)) return;

        NotesRepo::togglePin($id);
        Response::withSuccess(__('notes.pin_toggled'));
        Response::redirect('/notes');
    }

    public function archive(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $note = NotesRepo::find($id);
        if ($note && !$this->requireNoteAccess($note)) return;

        NotesRepo::archive($id);
        Response::withSuccess(__('notes.archived_msg'));
        Response::redirect('/notes');
    }

    public function restore(int $id): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $note = NotesRepo::find($id);
        if ($note && !$this->requireNoteAccess($note)) return;

        NotesRepo::restore($id);
        Response::withSuccess(__('notes.restored_msg'));
        Response::redirect('/notes');
    }
}
