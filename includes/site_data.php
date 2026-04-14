<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

$schoolName = 'Mubuga TSS';
$tagline = 'Technical education for builders, coders, and future problem-solvers.';

$programs = [
    [
        'title' => 'Software Development',
        'summary' => 'Students learn programming, web development, databases, systems thinking, and real-world digital problem solving.',
        'focus' => ['Coding practice', 'Project-based learning', 'Digital innovation'],
        'image' => 'assets/images/mb3.jfif',
        'link' => '/MUBUGA-TSS/pages/programs.php',
    ],
    [
        'title' => 'Electrical Technology',
        'summary' => 'Students build practical skills in electrical installation, maintenance, safety, troubleshooting, and technical workshop practice.',
        'focus' => ['Hands-on circuits', 'Power systems basics', 'Industry safety culture'],
        'image' => 'assets/images/IM4.jpg',
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
    'Competency-based learning shaped around technical careers',
    'A balanced environment for discipline, creativity, and teamwork',
    'Classroom theory connected directly to practical application',
];

$heroSlides = [
    [
        'eyebrow' => 'Welcome to Mubuga TSS',
        'title' => 'Excellence in technical education',
        'text' => 'A disciplined and practical learning environment where students prepare for modern careers through focused training.',
        'button' => 'Register',
    ],
    [
        'eyebrow' => 'Software Development',
        'title' => 'Build digital solutions that solve real problems',
        'text' => 'Students learn programming, systems thinking, web development, and project delivery with a strong practical mindset.',
        'button' => 'Register Now',
    ],
    [
        'eyebrow' => 'Electrical Technology',
        'title' => 'Learn through workshop-based technical practice',
        'text' => 'From installation to maintenance and safety, learners build the confidence to work with real electrical systems.',
        'button' => 'Register Now',
    ],
];

$featuredStories = [
    [
        'title' => 'Students in practical learning sessions',
        'text' => 'Learners engage in real technical tasks that connect classroom knowledge to hands-on problem solving.',
        'link' => '/MUBUGA-TSS/pages/news.php',
        'image' => 'assets/images/IM8.jpg',
    ],
    [
        'title' => 'Mubuga TSS school life and discipline',
        'text' => 'A focused environment that supports teamwork, confidence, and professional growth.',
        'link' => '/MUBUGA-TSS/pages/gallery.php',
        'image' => 'assets/images/mb2.jfif',
    ],
];

$leadership = [
    [
        'role' => 'School Leadership',
        'name' => 'Mubuga TSS Administration',
        'text' => 'Our leadership team is committed to growing a disciplined, innovative, and student-centered technical school community.',
        'photo' => 'assets/images/master.jpeg',
    ],
    [
        'role' => 'Academic Direction',
        'name' => 'Training and Learning Team',
        'text' => 'We support learners through strong instruction, practical coaching, and clear pathways into employment and further studies.',
        'photo' => 'assets/images/master.jpeg',
    ],
];

$news = [
    [
        'title' => 'Practical Learning Across Both Trades',
        'text' => 'Students engage in hands-on activities that connect classroom concepts to software projects and electrical workshop tasks.',
        'image' => 'assets/images/mb1.jfif',
        'link' => '/MUBUGA-TSS/pages/news.php',
    ],
    [
        'title' => 'Career-Focused Technical Education',
        'text' => 'Mubuga TSS prepares learners with relevant skills, confidence, and a mindset for solving real community and industry challenges.',
        'image' => 'assets/images/mb3.jfif',
        'link' => '/MUBUGA-TSS/pages/news.php',
    ],
    [
        'title' => 'School Community and Growth',
        'text' => 'Our school promotes discipline, collaboration, and innovation as core values in daily learning and school life.',
        'image' => 'assets/images/mb2.jfif',
        'link' => '/MUBUGA-TSS/pages/news.php',
    ],
];

$facilities = [
    [
        'title' => 'ICT and Coding Labs',
        'text' => 'Spaces where Software Development students practice programming, systems work, and collaborative digital projects.',
        'image' => 'assets/images/mb3.jfif',
    ],
    [
        'title' => 'Electrical Workshops',
        'text' => 'Hands-on training areas designed for installation practice, troubleshooting, equipment handling, and safety routines.',
        'image' => 'assets/images/IM4.jpg',
    ],
    [
        'title' => 'Student Support Environment',
        'text' => 'A school setting that encourages discipline, teamwork, mentorship, and strong daily learning habits.',
        'image' => 'assets/images/mb2.jfif',
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
        'title' => 'Students in the coding lab',
        'text' => 'Software Development learners working on practical projects and digital skills.',
        'image' => 'assets/images/mb3.jfif',
    ],
    [
        'title' => 'Electrical workshop practice',
        'text' => 'Hands-on training focused on installation, troubleshooting, and safe technical work.',
        'image' => 'assets/images/IM5.jpg',
    ],
    [
        'title' => 'School community life',
        'text' => 'A disciplined and supportive environment that encourages growth and teamwork.',
        'image' => 'assets/images/mb1.jfif',
    ],
    [
        'title' => 'Modern classroom facilities',
        'text' => 'Well-equipped learning spaces designed for focused technical education and skill development.',
        'image' => 'assets/images/IM1.jpg',
    ],
    [
        'title' => 'Practical electrical training',
        'text' => 'Students gaining hands-on experience with electrical systems and safety protocols.',
        'image' => 'assets/images/IM2.jpg',
    ],
    [
        'title' => 'Digital innovation hub',
        'text' => 'State-of-the-art computer labs where students develop programming and software skills.',
        'image' => 'assets/images/IM3.jpg',
    ],
    [
        'title' => 'Technical workshop environment',
        'text' => 'Dedicated spaces for practical learning in electrical technology and systems.',
        'image' => 'assets/images/IM4.jpg',
    ],
    [
        'title' => 'Collaborative learning sessions',
        'text' => 'Students working together on technical projects and problem-solving activities.',
        'image' => 'assets/images/IM6.jpg',
    ],
    [
        'title' => 'Professional development training',
        'text' => 'Focused instruction preparing students for careers in technical fields.',
        'image' => 'assets/images/IM7.jpg',
    ],
    [
        'title' => 'Hands-on technical practice',
        'text' => 'Real-world application of classroom knowledge through practical exercises.',
        'image' => 'assets/images/IM8.jpg',
    ],
    [
        'title' => 'Student engagement and focus',
        'text' => 'Dedicated learners committed to mastering technical skills and knowledge.',
        'image' => 'assets/images/IM9.jpg',
    ],
    [
        'title' => 'School leadership and guidance',
        'text' => 'Experienced educators providing mentorship and technical expertise.',
        'image' => 'assets/images/master.jpeg',
    ],
    [
        'title' => 'Campus life and activities',
        'text' => 'The vibrant school environment supporting both academic and personal growth.',
        'image' => 'assets/images/mb2.jfif',
    ],
    [
        'title' => 'Innovation in education',
        'text' => 'Modern teaching methods combining theory with practical application.',
        'image' => 'assets/images/IM10.jpeg',
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

    if (!empty($settings['school_motto'])) {
        $heroSlides[0]['title'] = $settings['school_motto'];
    }

    $programRows = $pdo->query('SELECT title, short_description FROM programs WHERE status = "active" ORDER BY id ASC')->fetchAll();
    if ($programRows) {
        $programImages = [
            'Software Development' => 'assets/images/mb3.jfif',
            'Electrical Technology' => 'assets/images/IM4.jpg',
        ];
        $programFocus = [
            'Software Development' => ['Coding practice', 'Project-based learning', 'Digital innovation'],
            'Electrical Technology' => ['Hands-on circuits', 'Power systems basics', 'Industry safety culture'],
        ];
        $programs = array_map(static function (array $row) use ($programImages, $programFocus): array {
            $title = $row['title'];
            return [
                'title' => $title,
                'summary' => $row['short_description'] ?: 'Technical learning pathway for Mubuga TSS students.',
                'focus' => $programFocus[$title] ?? ['Technical skills', 'Practical learning', 'Career readiness'],
                'image' => $programImages[$title] ?? 'assets/images/mb1.jfif',
                'link' => '/MUBUGA-TSS/pages/programs.php',
            ];
        }, $programRows);
    }

    $staffRows = $pdo->query('SELECT full_name, job_title, bio, photo FROM staff WHERE status = "active" ORDER BY is_featured DESC, display_order ASC, id ASC LIMIT 6')->fetchAll();
    if ($staffRows) {
        $leadership = array_map(static function (array $row): array {
            return [
                'role' => $row['job_title'],
                'name' => $row['full_name'],
                'text' => $row['bio'] ?: 'Mubuga TSS leadership committed to student success and technical excellence.',
                'photo' => $row['photo'] ?: 'assets/images/master.jpeg',
            ];
        }, $staffRows);
    }

    $newsRows = $pdo->query('SELECT title, summary, featured_image FROM news WHERE status = "published" ORDER BY published_at DESC, id DESC LIMIT 6')->fetchAll();
    if ($newsRows) {
        $news = array_map(static function (array $row): array {
            return [
                'title' => $row['title'],
                'text' => $row['summary'] ?: 'Latest update from Mubuga TSS.',
                'image' => $row['featured_image'] ?: 'assets/images/mb1.jfif',
                'link' => '/MUBUGA-TSS/pages/news.php',
            ];
        }, $newsRows);
    }

    $galleryRows = $pdo->query('SELECT title, caption, image_path FROM gallery ORDER BY is_featured DESC, id DESC LIMIT 9')->fetchAll();
    if ($galleryRows) {
        $gallery = array_map(static function (array $row): array {
            return [
                'title' => $row['title'],
                'text' => $row['caption'] ?: 'Mubuga TSS gallery image.',
                'image' => $row['image_path'],
            ];
        }, $galleryRows);
    }
}
