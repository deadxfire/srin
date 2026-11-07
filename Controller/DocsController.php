<?php

namespace Controller;

use Core\BaseController;

class DocsController extends BaseController
{
    /**
     * Complete Multipage Documentation Structure
     */
    protected static array $navigation = [
        'Prologue' => [
            'introduction'  => 'Introduction & Philosophy',
            'installation'  => 'Installation & Quickstart',
            'cli'           => 'Srin CLI Tool Reference',
        ],
        'The Basics' => [
            'routing'       => 'Routing & Named Routes',
            'controllers'   => 'Controllers & Actions',
            'views'         => 'Templates, Layouts & Slots',
            'assets'        => 'Assets & Image Linking',
        ],
        'Database & ORM' => [
            'database'      => 'Database Options & Connections',
            'models'        => 'ActiveRecord Models & CRUD',
            'query-builder' => 'QueryBuilder Superpowers',
            'relationships' => 'Relationships & Eager Loading',
            'pagination'    => 'Database Pagination',
            'migrations'    => 'Schema & Migrations',
            'seeders'       => 'Database Seeders',
        ],
        'Security & Extras' => [
            'security'      => 'Security & Defense-in-Depth',
            'mailer'        => 'Mail Transfer & SMTP',
            'helpers'       => 'Global Helpers Reference',
        ],
    ];

    /**
     * Display docs index (landing on introduction).
     */
    public function index(): string
    {
        return $this->show('introduction');
    }

    /**
     * Display a specific documentation chapter.
     */
    public function show(string $topic): string
    {
        $topic = strtolower(trim($topic));
        $allTopics = self::getFlatTopicsList();

        if (!array_key_exists($topic, $allTopics)) {
            $this->abort(404, "Documentation page [{$topic}] does not exist.");
        }

        $meta = $allTopics[$topic];
        $slugs = array_keys($allTopics);
        $currentIndex = array_search($topic, $slugs, true);

        $prev = $currentIndex > 0 ? [
            'slug'  => $slugs[$currentIndex - 1],
            'title' => $allTopics[$slugs[$currentIndex - 1]]['title'],
        ] : null;

        $next = $currentIndex < (count($slugs) - 1) ? [
            'slug'  => $slugs[$currentIndex + 1],
            'title' => $allTopics[$slugs[$currentIndex + 1]]['title'],
        ] : null;

        return $this->view("docs/{$topic}", [
            'title'        => $meta['title'] . ' - Srin Engine Documentation',
            'currentTopic' => $topic,
            'topicTitle'   => $meta['title'],
            'category'     => $meta['category'],
            'navigation'   => self::$navigation,
            'prev'         => $prev,
            'next'         => $next,
        ]);
    }

    /**
     * Flatten navigation tree for linear prev/next traversal.
     */
    public static function getFlatTopicsList(): array
    {
        $flat = [];
        foreach (self::$navigation as $category => $topics) {
            foreach ($topics as $slug => $title) {
                $flat[$slug] = [
                    'title'    => $title,
                    'category' => $category,
                ];
            }
        }
        return $flat;
    }

    /**
     * Get the full navigation tree.
     */
    public static function getNavigation(): array
    {
        return self::$navigation;
    }
}
