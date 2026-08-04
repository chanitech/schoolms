<?php

namespace App\Http\Middleware;

use App\Models\Budget;
use App\Models\Invoice;
use App\Models\Loan;
use App\Models\Suggestion;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class InjectMenuBadges
{
    /**
     * Live "pending" counts for sidebar menu items, keyed by the item's
     * 'route' name. Mirrors each list controller's own pending-query
     * exactly, so the badge always matches what the user sees on click.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $counts = $this->pendingCounts(Auth::user());

            if (array_filter($counts) !== []) {
                config(['adminlte.menu' => $this->applyBadges(config('adminlte.menu', []), $counts)]);
            }
        }

        return $next($request);
    }

    private function pendingCounts($user): array
    {
        $counts = [];

        if ($user->hasRole('Admin')) {
            $counts['treasurer.loans.pending'] = Loan::where('status', 'pending')->count();
        } elseif ($user->hasRole('chief-accountant')) {
            $counts['treasurer.loans.pending'] = Loan::where('status', 'pending')->where('approval_level', 0)->count();
        } elseif ($user->hasRole('accountant')) {
            $counts['treasurer.loans.pending'] = Loan::where('status', 'pending')->where('approval_level', 1)->count();
        } elseif ($user->hasRole('treasurer')) {
            $counts['treasurer.loans.pending'] = Loan::where('status', 'pending')->where('approval_level', 2)->count();
        }

        if ($user->can('view pending approvals')) {
            $counts['finance.budgets.pending'] = Budget::where('status', 'pending')->where('current_step', 'do')->count();
        }

        if ($user->can('approve invoices')) {
            $counts['finance.invoices.do'] = Invoice::where('status', 'pending')->count();
        }

        if ($user->can('manage suggestions')) {
            // Menu entry is keyed by 'url' (see config/adminlte.php), not 'route'.
            $counts['suggestions/manage'] = Suggestion::where('status', 'new')->count();
        }

        return $counts;
    }

    private function applyBadges(array $menu, array $counts): array
    {
        foreach ($menu as &$item) {
            $key = $item['route'] ?? $item['url'] ?? null;

            if ($key !== null && isset($counts[$key])) {
                if ($counts[$key] > 0) {
                    $item['label'] = $counts[$key];
                } else {
                    unset($item['label']);
                }
            }

            if (isset($item['submenu'])) {
                $item['submenu'] = $this->applyBadges($item['submenu'], $counts);
            }
        }

        return $menu;
    }
}
