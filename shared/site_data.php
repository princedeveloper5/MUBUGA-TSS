<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/content_metrics.php';

function resolveSiteImage(string $path, string $fallback = 'assets/images/students.jfif'): string
{
    $normalizedPath = trim(str_replace('\\', '/', $path));
    if ($normalizedPath !== '') {
        $absolutePath = __DIR__ . '/../' . ltrim($normalizedPath, '/');
        if (is_file($absolutePath)) {
            return $normalizedPath;
        }
    }

    return $fallback;
}

function normalizeNewsCategory(string $category): string
{
    $normalized = strtolower(trim($category));
    return match ($normalized) {
        'event', 'events' => 'events',
        'announcement', 'announcements' => 'announcements',
        default => 'news',
    };
}

function newsCategoryLabel(string $category): string
{
    return match (normalizeNewsCategory($category)) {
        'events' => 'Events',
        'announcements' => 'Announcements',
        default => 'News',
    };
}

function formatPublishedDate(?string $publishedAt): string
{
    $value = trim((string) $publishedAt);
    if ($value === '') {
        return 'Recent update';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return 'Recent update';
    }

    return date('F j, Y', $timestamp);
}

function encodeNewsContent(string $content, string $category): string
{
    return '[category:' . normalizeNewsCategory($category) . ']' . "\n" . trim($content);
}

function decodeNewsContent(?string $content): array
{
    $rawContent = trim((string) $content);
    $category = 'news';

    if ($rawContent !== '' && preg_match('/^\[category:(events|announcements|news)\]\s*/i', $rawContent, $matches) === 1) {
        $category = normalizeNewsCategory($matches[1]);
        $rawContent = trim((string) preg_replace('/^\[category:(events|announcements|news)\]\s*/i', '', $rawContent, 1));
    }

    return [
        'category' => $category,
        'content' => $rawContent,
    ];
}

function isVideoMediaPath(string $path): bool
{
    $normalized = strtolower(trim($path));
    if ($normalized === '') {
        return false;
    }

    if (preg_match('/\.(mp4|webm|ogg)$/', $normalized) === 1) {
        return true;
    }

    return str_contains($normalized, 'youtube.com/') || str_contains($normalized, 'youtu.be/') || str_contains($normalized, 'vimeo.com/');
}

function parseGalleryCategory(?string $rawCategory, string $path = ''): array
{
    $raw = strtolower(trim((string) $rawCategory));
    $mediaType = isVideoMediaPath($path) ? 'video' : 'image';
    $category = 'campus';

    if ($raw !== '') {
        $parts = explode(':', $raw, 2);
        if (in_array($parts[0], ['image', 'video'], true)) {
            $mediaType = $parts[0];
            $category = trim($parts[1] ?? '') !== '' ? trim($parts[1]) : $category;
        } else {
            $category = $raw;
            if (str_contains($raw, 'video')) {
                $mediaType = 'video';
                $category = trim(str_replace('video', '', $raw), ' :-') ?: 'campus';
            }
        }
    }

    return [
        'media_type' => $mediaType,
        'category' => $category,
        'category_label' => ucwords(str_replace(['-', '_'], ' ', $category)),
    ];
}

$schoolName = 'Mubuga TSS';
$tagline = 'Short training. Real skills. Strong futures.';

$imageSet = [
    'logo' => 'assets/images/MUBUGA LOGO SN.PNG',
    'students' => 'assets/images/students.jfif',
    'software_primary' => 'assets/images/software development.jpg',
    'software_secondary' => 'assets/images/software development 2.jfif',
    'electrical_primary' => 'assets/images/electrical technology.JPG',
    'electrical_secondary' => 'assets/images/electrical technology 2.jpeg',
    'playground' => 'assets/images/playground.jpg',
    'school_1' => 'assets/images/school view 1.jpg',
    'school_2' => 'assets/images/school view 2.jpg',
    'school_3' => 'assets/images/school view 3.jpg',
    'school_4' => 'assets/images/school view 4.jpg',
    'school_5' => 'assets/images/school view 5.jpg',
    'school_6' => 'assets/images/school view 6.jpg',
    'school_7' => 'assets/images/school view 7.jfif',
    'leader' => 'assets/images/master.jpeg',
];

$programs = [
    [
        'title' => 'Software Development',
        'summary' => 'Learn coding, web building, and digital problem-solving.',
        'focus' => ['Coding practice', 'Project-based learning', 'Digital innovation'],
        'image' => $imageSet['software_primary'],
        'link' => '/MUBUGA-TSS/pages/programs.php',
    ],
    [
        'title' => 'Electrical Technology',
        'summary' => 'Build strong skills in installation, safety, and maintenance.',
        'focus' => ['Hands-on circuits', 'Power systems basics', 'Industry safety culture'],
        'image' => $imageSet['electrical_primary'],
        'link' => '/MUBUGA-TSS/pages/programs.php',
    ],
];

$stats = [
    ['value' => '2', 'label' => 'Specialized trades'],
    ['value' => '100%', 'label' => 'Technical learning focus'],
    ['value' => 'Practical', 'label' => 'Workshop-driven training'],
    ['value' => 'Future-ready', 'label' => 'Skills for modern careers'],
];

$highlights = [
    'Skills first',
    'Discipline every day',
    'Practice with purpose',
];

$heroSlides = [
    [
        'eyebrow' => 'Welcome to Mubuga TSS',
        'title' => 'Technical skills for real life',
        'text' => 'We train students to learn fast, work well, and build confidently.',
        'button' => 'Register',
        'link' => '/MUBUGA-TSS/pages/admissions.php',
        'image' => resolveSiteImage($imageSet['students']),
        'spotlight' => 'Campus and school community',
    ],
    [
        'eyebrow' => 'Software Development',
        'title' => 'Code useful digital solutions',
        'text' => 'Students learn programming, web development, and project thinking through practice.',
        'button' => 'Register Now',
        'link' => '/MUBUGA-TSS/pages/programs.php',
        'image' => resolveSiteImage($imageSet['software_secondary']),
        'spotlight' => 'Coding labs and digital projects',
    ],
    [
        'eyebrow' => 'Electrical Technology',
        'title' => 'Train with real electrical practice',
        'text' => 'Learners grow through installation, maintenance, safety, and workshop sessions.',
        'button' => 'Register Now',
        'link' => '/MUBUGA-TSS/pages/programs.php',
        'image' => resolveSiteImage($imageSet['electrical_secondary']),
        'spotlight' => 'Workshop practice and electrical systems',
    ],
];

$featuredStories = [
    [
        'title' => 'Students learning by doing',
        'text' => 'Classroom knowledge meets practical work every day.',
        'link' => '/MUBUGA-TSS/pages/news.php',
        'image' => $imageSet['students'],
    ],
    [
        'title' => 'School life with discipline',
        'text' => 'Students grow in teamwork, confidence, and focus.',
        'link' => '/MUBUGA-TSS/pages/gallery.php',
        'image' => $imageSet['playground'],
    ],
];

$leadership = [
    [
        'role' => 'School Leadership',
        'name' => 'Mubuga TSS Administration',
        'text' => 'We lead a school culture built on discipline, skills, and student growth.',
        'photo' => $imageSet['leader'],
    ],
    [
        'role' => 'Academic Direction',
        'name' => 'Training and Learning Team',
        'text' => 'We guide learners with clear teaching, practice, and career direction.',
        'photo' => $imageSet['leader'],
    ],
];

$news = [
    [
        'title' => 'Open Day for New Applicants',
        'text' => 'Families can visit the campus, meet staff, and explore our programs.',
        'category' => 'events',
        'slug' => 'open-day-for-new-applicants',
        'content' => 'Families can visit the campus, meet staff, and explore our programs.',
        'image' => $imageSet['students'],
        'published_at' => '2026-01-18 09:00:00',
        'link' => '/MUBUGA-TSS/pages/news.php?slug=open-day-for-new-applicants',
    ],
    [
        'title' => 'Admission Requirements Released',
        'text' => 'Applicants can now check key requirements before registration.',
        'category' => 'announcements',
        'slug' => 'admission-requirements-released',
        'content' => 'Applicants can now check key requirements before registration.',
        'image' => $imageSet['software_primary'],
        'published_at' => '2026-02-08 08:30:00',
        'link' => '/MUBUGA-TSS/pages/news.php?slug=admission-requirements-released',
    ],
    [
        'title' => 'Students Build Real Projects',
        'text' => 'Learning at Mubuga TSS stays practical, creative, and career-focused.',
        'category' => 'news',
        'slug' => 'students-build-real-projects',
        'content' => 'Learning at Mubuga TSS stays practical, creative, and career-focused.',
        'image' => $imageSet['school_7'],
        'published_at' => '2026-03-02 10:15:00',
        'link' => '/MUBUGA-TSS/pages/news.php?slug=students-build-real-projects',
    ],
];

$facilities = [
    [
        'title' => 'ICT and Coding Labs',
        'text' => 'Focused spaces for coding, systems work, and digital projects.',
        'image' => $imageSet['software_primary'],
    ],
    [
        'title' => 'Electrical Workshops',
        'text' => 'Hands-on areas for installation, troubleshooting, and safety practice.',
        'image' => $imageSet['electrical_primary'],
    ],
    [
        'title' => 'Student Support Environment',
        'text' => 'A calm school environment built on support, teamwork, and discipline.',
        'image' => $imageSet['school_3'],
    ],
];

$admissions = [
    'Choose the trade that matches your interest and career direction.',
    'Prepare school documents and registration information.',
    'Contact the school for guidance on admission and reporting requirements.',
];

$contacts = [
    ['label' => 'Email', 'value' => 'info@mubugatss.rw'],
    ['label' => 'Phone', 'value' => '+250 7XX XXX XXX'],
    ['label' => 'Location', 'value' => 'Mubuga, Rwanda'],
];

$institutionCards = [
    [
        'label' => 'School Identity',
        'title' => 'Focused technical school',
        'text' => 'We prepare learners through practical trades and disciplined study.',
    ],
    [
        'label' => 'Campus Focus',
        'title' => 'Learning by practice',
        'text' => 'Workshops, projects, and class lessons build real confidence.',
    ],
    [
        'label' => 'Student Pathway',
        'title' => 'Ready for the future',
        'text' => 'Students grow toward work, self-reliance, and further study.',
    ],
];

$welcomeHighlights = [
    'Discipline and responsibility',
    'Learning linked to real work',
    'Mentorship and teamwork',
];

$siteMeta = [
    'logo_path' => $imageSet['logo'],
    'logo_size' => 52,
    'facebook_url' => '#',
    'instagram_url' => '#',
    'twitter_url' => '#',
    'theme_mode' => 'light',
    'homepage_notice' => '',
];

$values = [
    'Discipline',
    'Integrity',
    'Practical Excellence',
    'Innovation',
    'Teamwork',
    'Responsibility',
];

$gallery = [
    [
        'title' => 'Coding lab in action',
        'text' => 'Students build digital skills through practical work.',
        'category' => 'image:academics',
        'image' => $imageSet['software_primary'],
    ],
    [
        'title' => 'Electrical workshop practice',
        'text' => 'Students train in installation, troubleshooting, and safety.',
        'category' => 'image:workshops',
        'image' => $imageSet['electrical_secondary'],
    ],
    [
        'title' => 'Campus life',
        'text' => 'A supportive environment for growth and teamwork.',
        'category' => 'image:campus',
        'image' => $imageSet['students'],
    ],
    [
        'title' => 'Learning spaces',
        'text' => 'Well-prepared rooms for focused technical learning.',
        'category' => 'image:campus',
        'image' => $imageSet['school_1'],
    ],
    [
        'title' => 'Practical electrical training',
        'text' => 'Students gain real experience with systems and safety.',
        'category' => 'image:workshops',
        'image' => $imageSet['electrical_primary'],
    ],
    [
        'title' => 'Digital skills hub',
        'text' => 'Computer labs that support programming and software practice.',
        'category' => 'image:academics',
        'image' => $imageSet['software_secondary'],
    ],
    [
        'title' => 'Workshop environment',
        'text' => 'Dedicated spaces for practical technical learning.',
        'category' => 'image:workshops',
        'image' => $imageSet['electrical_secondary'],
    ],
    [
        'title' => 'Collaborative learning',
        'text' => 'Students work together on technical tasks and projects.',
        'category' => 'image:academics',
        'image' => $imageSet['school_2'],
    ],
    [
        'title' => 'Career-focused training',
        'text' => 'Focused instruction that prepares students for technical careers.',
        'category' => 'image:academics',
        'image' => $imageSet['school_6'],
    ],
    [
        'title' => 'Hands-on practice',
        'text' => 'Classroom learning turns into practical experience.',
        'category' => 'image:campus',
        'image' => $imageSet['playground'],
    ],
    [
        'title' => 'Student focus',
        'text' => 'Learners stay engaged and committed to their goals.',
        'category' => 'image:campus',
        'image' => $imageSet['school_5'],
    ],
    [
        'title' => 'Leadership and guidance',
        'text' => 'School leaders support learning, direction, and growth.',
        'category' => 'image:staff',
        'image' => $imageSet['leader'],
    ],
    [
        'title' => 'Campus activities',
        'text' => 'School life supports both learning and personal growth.',
        'category' => 'image:campus',
        'image' => $imageSet['school_7'],
    ],
    [
        'title' => 'Innovation in learning',
        'text' => 'Teaching combines clear theory with practical action.',
        'category' => 'image:academics',
        'image' => $imageSet['school_4'],
    ],
];

$pdo = getDatabaseConnection();

if ($pdo instanceof PDO) {
    $settingsRows = $pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
    $settings = [];
    foreach ($settingsRows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    $schoolName = $settings['school_name'] ?? $schoolName;
    $contacts = [
        ['label' => 'Email', 'value' => $settings['school_email'] ?? $contacts[0]['value']],
        ['label' => 'Phone', 'value' => $settings['school_phone'] ?? $contacts[1]['value']],
        ['label' => 'Location', 'value' => $settings['school_address'] ?? $contacts[2]['value']],
    ];
    $siteMeta['logo_path'] = $settings['school_logo'] ?? $siteMeta['logo_path'];
    $siteMeta['logo_size'] = max(32, min(140, (int) ($settings['site_logo_size'] ?? $siteMeta['logo_size'])));
    $siteMeta['facebook_url'] = $settings['school_facebook'] ?? $siteMeta['facebook_url'];
    $siteMeta['instagram_url'] = $settings['school_instagram'] ?? $siteMeta['instagram_url'];
    $siteMeta['twitter_url'] = $settings['school_twitter'] ?? $siteMeta['twitter_url'];
    $siteMeta['theme_mode'] = ($settings['theme_mode'] ?? 'light') === 'dark' ? 'dark' : 'light';
    $siteMeta['homepage_notice'] = trim((string) ($settings['homepage_notice'] ?? ''));

    if (!empty($settings['school_motto'])) {
        $heroSlides[0]['title'] = $settings['school_motto'];
    }

    $programRows = $pdo->query('SELECT title, short_description, cover_image FROM programs WHERE status = "active" ORDER BY id ASC')->fetchAll();
    if ($programRows) {
        $programImages = [
            'Software Development' => $imageSet['software_primary'],
            'Electrical Technology' => $imageSet['electrical_primary'],
        ];
        $programFocus = [
            'Software Development' => ['Coding practice', 'Project-based learning', 'Digital innovation'],
            'Electrical Technology' => ['Hands-on circuits', 'Power systems basics', 'Industry safety culture'],
        ];
        $programs = array_map(function (array $row) use ($programImages, $programFocus, $imageSet): array {
            $title = $row['title'];
            return [
                'title' => $title,
                'summary' => $row['short_description'] ?: 'A practical pathway for technical learning.',
                'focus' => $programFocus[$title] ?? ['Technical skills', 'Practical learning', 'Career readiness'],
                'image' => resolveSiteImage($row['cover_image'] ?: ($programImages[$title] ?? $imageSet['students'])),
                'link' => '/MUBUGA-TSS/pages/programs.php',
            ];
        }, $programRows);
    }

    $staffRows = $pdo->query('SELECT full_name, job_title, bio, photo FROM staff WHERE status = "active" ORDER BY is_featured DESC, display_order ASC, id ASC LIMIT 6')->fetchAll();
    if ($staffRows) {
        $leadership = array_map(function (array $row) use ($imageSet): array {
            return [
                'role' => $row['job_title'],
                'name' => $row['full_name'],
                'text' => $row['bio'] ?: 'Committed to student success and technical excellence.',
                'photo' => resolveSiteImage($row['photo'] ?: $imageSet['leader'], $imageSet['leader']),
            ];
        }, $staffRows);
    }

    ensureContentMetricTables($pdo);

    $newsRows = $pdo->query('
        SELECT
            news.id,
            news.title,
            news.slug,
            news.summary,
            news.content,
            news.featured_image,
            COALESCE(news_admin_meta.scheduled_for, news.published_at) AS published_at,
            COALESCE(news_admin_meta.is_pinned, 0) AS is_pinned,
            COALESCE(news_admin_meta.view_count, 0) AS view_count
        FROM news
        LEFT JOIN news_admin_meta ON news_admin_meta.news_id = news.id
        WHERE news.status = "published"
          AND COALESCE(news_admin_meta.scheduled_for, news.published_at, NOW()) <= NOW()
        ORDER BY COALESCE(news_admin_meta.is_pinned, 0) DESC,
                 COALESCE(news_admin_meta.scheduled_for, news.published_at) DESC,
                 news.id DESC
        LIMIT 18
    ')->fetchAll();
    if ($newsRows) {
        $news = array_map(function (array $row) use ($imageSet): array {
            $decodedContent = decodeNewsContent($row['content'] ?? '');
            return [
                'id' => (int) ($row['id'] ?? 0),
                'title' => $row['title'],
                'slug' => $row['slug'] ?? '',
                'category' => $decodedContent['category'],
                'text' => $row['summary'] ?: 'Latest update from Mubuga TSS.',
                'content' => $decodedContent['content'] ?: $row['summary'] ?: 'Latest update from Mubuga TSS.',
                'image' => resolveSiteImage($row['featured_image'] ?: $imageSet['students']),
                'published_at' => $row['published_at'] ?? '',
                'is_pinned' => (int) ($row['is_pinned'] ?? 0),
                'view_count' => (int) ($row['view_count'] ?? 0),
                'link' => '/MUBUGA-TSS/pages/news.php' . (!empty($row['slug']) ? '?slug=' . urlencode((string) $row['slug']) : ''),
            ];
        }, $newsRows);
    }

    $galleryRows = $pdo->query('
        SELECT
            gallery.id,
            gallery.title,
            gallery.caption,
            gallery.image_path,
            gallery.category,
            COALESCE(gallery_admin_meta.view_count, 0) AS view_count,
            COALESCE(gallery_admin_meta.download_count, 0) AS download_count
        FROM gallery
        LEFT JOIN gallery_admin_meta ON gallery_admin_meta.gallery_id = gallery.id
        ORDER BY gallery.is_featured DESC, gallery.id DESC
        LIMIT 9
    ')->fetchAll();
    if ($galleryRows) {
        $gallery = array_map(function (array $row): array {
            $media = parseGalleryCategory($row['category'] ?? '', (string) $row['image_path']);
            return [
                'id' => (int) ($row['id'] ?? 0),
                'title' => $row['title'],
                'text' => $row['caption'] ?: 'A moment from Mubuga TSS.',
                'image' => isVideoMediaPath((string) $row['image_path']) ? (string) $row['image_path'] : resolveSiteImage((string) $row['image_path']),
                'category' => $media['category'],
                'media_type' => $media['media_type'],
                'category_label' => $media['category_label'],
                'view_count' => (int) ($row['view_count'] ?? 0),
                'download_count' => (int) ($row['download_count'] ?? 0),
            ];
        }, $galleryRows);
    }
}

function sitePageContent(string $slug, array $defaults): array
{
    $pdo = getDatabaseConnection();
    $resolvedDefaults = $defaults;
    $resolvedDefaults['image'] = resolveSiteImage((string) ($defaults['image'] ?? ''));

    if (!$pdo instanceof PDO) {
        return $resolvedDefaults;
    }

    $stmt = $pdo->prepare('SELECT title, excerpt, content, banner_image, status FROM pages WHERE slug = :slug LIMIT 1');
    $stmt->execute(['slug' => $slug]);
    $page = $stmt->fetch();

    if (!$page || ($page['status'] ?? 'draft') !== 'published') {
        return $resolvedDefaults;
    }

    return [
        'title' => $page['title'] ?: $resolvedDefaults['title'],
        'excerpt' => $page['excerpt'] ?: $resolvedDefaults['excerpt'],
        'content' => $page['content'] ?: $resolvedDefaults['content'],
        'image' => resolveSiteImage((string) ($page['banner_image'] ?: $resolvedDefaults['image'])),
    ];
}
