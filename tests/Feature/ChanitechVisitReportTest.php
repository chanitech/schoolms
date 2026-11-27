<?php

namespace Tests\Feature;

use App\Http\Middleware\ReportVisitToChanitech;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * Exercises the middleware directly (no database), so it runs wherever the suite does.
 */
class ChanitechVisitReportTest extends TestCase
{
    private const URL = 'https://chanitech.example/analytics/visit';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Http::fake([self::URL => Http::response(['status' => 'ok'])]);
        config(['services.chanitech_analytics.url' => null, 'services.chanitech_analytics.token' => null, 'services.chanitech_analytics.salt' => 'pepper']);
    }

    private function enable(): void
    {
        config(['services.chanitech_analytics.url' => self::URL, 'services.chanitech_analytics.token' => 'shared-secret']);
    }

    /** A signed-in person without touching the database. */
    private function person(int $id = 5, string $role = 'guardian'): object
    {
        return new class($id, $role)
        {
            public function __construct(private int $id, private string $role) {}

            public function getAuthIdentifier(): int { return $this->id; }

            public function getRoleNames() { return collect([$this->role]); }

            public string $name = 'Sensitive Name';

            public string $email = 'private@example.org';
        };
    }

    private function visit(string $uri = '/students/42/edit?secret=1', ?object $user = null, string $method = 'GET', array $server = [], int $status = 200): void
    {
        $request = Request::create($uri, $method, [], [], [], $server);
        $request->setUserResolver(fn () => $user);

        (new ReportVisitToChanitech)->terminate($request, new Response('ok', $status));
    }

    public function test_nothing_is_sent_until_it_is_configured(): void
    {
        $this->visit('/dashboard', $this->person());

        Http::assertNothingSent();
    }

    public function test_a_visit_is_reported_with_only_anonymous_details(): void
    {
        $this->enable();
        app()->instance('currentSchool', (object) ['slug' => 'kitungwa', 'name' => 'Kitungwa Adventist Secondary School']);

        $this->visit('/students/42/edit?secret=1', $this->person(5, 'guardian'));

        Http::assertSent(function (HttpRequest $request) {
            $body = $request->data();
            $json = json_encode($body);

            return $request->url() === self::URL
                && $request->hasHeader('Authorization', 'Bearer shared-secret')
                && $body['source'] === 'shulepro'
                && $body['authenticated'] === true
                && $body['role'] === 'guardian'
                && $body['school_slug'] === 'kitungwa'
                && $body['school_name'] === 'Kitungwa Adventist Secondary School'
                && $body['visitor_key'] === hash('sha256', 'shulepro|user:5|pepper')
                && $body['path'] === '/students/{id}/edit'          // ids and the query string are dropped
                && ! str_contains($json, 'Sensitive Name')
                && ! str_contains($json, 'private@example.org')
                && ! str_contains($json, 'secret');
        });
    }

    public function test_a_guest_is_reported_without_a_role(): void
    {
        $this->enable();

        $this->visit('/login', null);

        Http::assertSent(fn (HttpRequest $r) => $r->data()['authenticated'] === false && $r->data()['role'] === null && $r->data()['path'] === '/login');
    }

    public function test_a_person_is_reported_once_per_day(): void
    {
        $this->enable();

        $this->visit('/dashboard', $this->person(7));
        $this->visit('/students', $this->person(7));
        $this->visit('/dashboard', $this->person(8));      // someone else: reported

        Http::assertSentCount(2);
    }

    public function test_only_real_page_views_are_reported(): void
    {
        $this->enable();
        $user = $this->person(9);

        $this->visit('/dashboard', $user, 'POST');                                              // not a GET
        $this->visit('/dashboard', $user, 'GET', ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);  // ajax
        $this->visit('/dashboard', $user, 'GET', ['HTTP_ACCEPT' => 'application/json']);          // wants JSON
        $this->visit('/missing', $user, 'GET', [], 404);                                        // error page
        $this->visit('/livewire/update', $user);                                                // internals
        $this->visit('/api/public/news', $user);

        Http::assertNothingSent();
    }

    public function test_a_broken_analytics_server_never_breaks_shulepro(): void
    {
        $this->enable();
        Http::fake([self::URL => Http::response('down', 500)]);

        $this->visit('/dashboard', $this->person(10));

        $this->assertTrue(true);   // reaching here without an exception is the point
    }

    public function test_a_connection_failure_is_swallowed(): void
    {
        $this->enable();
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('no route'));

        $this->visit('/dashboard', $this->person(11));

        $this->assertTrue(true);
    }

    public function test_the_android_app_is_recognised_by_its_webview(): void
    {
        $this->enable();

        $this->visit('/dashboard', $this->person(12), 'GET', ['HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; Android 14; Pixel 7 Build/AP1A; wv) AppleWebKit/537.36 Chrome/120 Mobile Safari/537.36']);
        $this->visit('/dashboard', $this->person(13), 'GET', ['HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) Chrome/120 Safari/537.36']);

        Http::assertSent(fn (HttpRequest $r) => $r->data()['in_app'] === true);
        Http::assertSent(fn (HttpRequest $r) => $r->data()['in_app'] === false);
    }
}
