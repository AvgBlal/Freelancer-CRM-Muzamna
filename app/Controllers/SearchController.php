<?php
/**
 * Search Controller
 * Global search across all modules
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Repositories\SearchRepo;

class SearchController
{
    public function index(): void
    {
        Auth::requireAuth();

        $query = trim($_GET['q'] ?? '');
        $results = [];
        $total = 0;

        if (mb_strlen($query) >= 2) {
            if (Auth::isEmployee()) {
                $results = SearchRepo::searchForEmployee($query, Auth::id());
            } else {
                $results = SearchRepo::search($query);
            }
            $total = count($results);
        }

        require __DIR__ . '/../Views/search/index.php';
    }
}
