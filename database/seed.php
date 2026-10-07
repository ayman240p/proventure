<?php
/**
 * database/seed.php
 * Comprehensive seeder script for ProVenture.
 * Populates realistic Malaysian campus freelancers, clients, services, and peer reviews.
 * Can be run from CLI: php database/seed.php
 * Or via browser: http://localhost/cshub/database/seed.php
 */

require_once __DIR__ . '/../config/database.php';

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    echo "<!DOCTYPE html><html><head><title>ProVenture Database Seeder</title>";
    echo "<style>body { font-family: system-ui, sans-serif; padding: 2rem; background: #0b1120; color: #f1f5f9; }";
    echo "h1, h2 { color: #2edb72; } pre { background: #1e293b; padding: 1rem; border-radius: 8px; border: 1px solid #334155; overflow-x: auto; }";
    echo ".badge { background: #128C7E; color: #fff; padding: 2px 8px; border-radius: 99px; font-size: 0.8rem; }";
    echo ".success { color: #4ade80; } table { width: 100%; border-collapse: collapse; margin: 1rem 0; background: #1e293b; border-radius: 8px; overflow: hidden; }";
    echo "th, td { padding: 10px 14px; text-align: left; border-bottom: 1px solid #334155; } th { background: #0f172a; color: #38bdf8; }</style></head><body>";
    echo "<h1>🌱 ProVenture Sample Data Seeder</h1>";
}

function out($msg) {
    global $isCli;
    if ($isCli) {
        echo $msg . "\n";
    } else {
        echo "<p class='success'>" . htmlspecialchars($msg) . "</p>";
    }
}

// 1. Password Hash for standard password: 'password123'
$defaultPassword = 'password123';
$passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);

out("🔑 Using standard password for sample accounts: 'password123'");

// 2. Sample Freelancers
$freelancers = [
    [
        'name'  => 'Ahmad Kamil',
        'email' => 'ahmad.kamil@cshub.com',
        'phone' => '601123456781',
        'role'  => 'freelancer',
    ],
    [
        'name'  => 'Nurul Hidayah',
        'email' => 'nurul.hidayah@cshub.com',
        'phone' => '60178901234',
        'role'  => 'freelancer',
    ],
    [
        'name'  => 'Kevin Tan',
        'email' => 'kevin.tan@cshub.com',
        'phone' => '60123498765',
        'role'  => 'freelancer',
    ],
    [
        'name'  => 'Hafiz Ramli',
        'email' => 'hafiz.ramli@cshub.com',
        'phone' => '60134567890',
        'role'  => 'freelancer',
    ],
    [
        'name'  => 'Farah Nabilah',
        'email' => 'farah.nabilah@cshub.com',
        'phone' => '60198765432',
        'role'  => 'freelancer',
    ],
    [
        'name'  => 'Danial Lee',
        'email' => 'danial.lee@cshub.com',
        'phone' => '60167890123',
        'role'  => 'freelancer',
    ],
    [
        'name'  => 'Siti Sarah',
        'email' => 'siti.sarah@cshub.com',
        'phone' => '60182345678',
        'role'  => 'freelancer',
    ],
    [
        'name'  => 'Priyadarshini Mohan',
        'email' => 'priya.mohan@cshub.com',
        'phone' => '60145678901',
        'role'  => 'freelancer',
    ],
    [
        'name'  => 'Ayman Aqasyah',
        'email' => 'aymanaqasyah1@gmail.com',
        'phone' => '601129392419',
        'role'  => 'freelancer',
    ]
];

// 3. Sample Clients
$clients = [
    [
        'name'  => 'Adam Farhan',
        'email' => 'adam.farhan@gmail.com',
        'phone' => '60129876543',
        'role'  => 'client',
    ],
    [
        'name'  => 'Siti Aisyah',
        'email' => 'siti.aisyah@gmail.com',
        'phone' => '60139876543',
        'role'  => 'client',
    ],
    [
        'name'  => 'Chloe Tan',
        'email' => 'chloe.tan@gmail.com',
        'phone' => '60149876543',
        'role'  => 'client',
    ],
    [
        'name'  => 'Marcus Wong',
        'email' => 'marcus.wong@gmail.com',
        'phone' => '60169876543',
        'role'  => 'client',
    ],
    [
        'name'  => 'Nur Aina Syafiah',
        'email' => 'aina.syafiah@gmail.com',
        'phone' => '60179876543',
        'role'  => 'client',
    ],
    [
        'name'  => 'Bryan Lim',
        'email' => 'bryan.lim@gmail.com',
        'phone' => '60189876543',
        'role'  => 'client',
    ],
    [
        'name'  => 'Haziq Izzat',
        'email' => 'haziq.izzat@gmail.com',
        'phone' => '60199876543',
        'role'  => 'client',
    ],
    [
        'name'  => 'Ali bin Ahmad',
        'email' => 'aliahmad@gmail.com',
        'phone' => '60123456789',
        'role'  => 'client',
    ]
];

// Insert/update users
$userMap = []; // email -> id

$allUsers = array_merge($freelancers, $clients);
foreach ($allUsers as $u) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$u['email']]);
    $existing = $stmt->fetch();

    if ($existing) {
        $userId = $existing['id'];
        $update = $pdo->prepare("UPDATE users SET name = ?, phone = ?, role = ?, password = ? WHERE id = ?");
        $update->execute([$u['name'], $u['phone'], $u['role'], $passwordHash, $userId]);
    } else {
        $insert = $pdo->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
        $insert->execute([$u['name'], $u['email'], $passwordHash, $u['phone'], $u['role']]);
        $userId = $pdo->lastInsertId();
    }
    $userMap[$u['email']] = $userId;
}

out("✓ Upserted " . count($freelancers) . " Freelancer accounts and " . count($clients) . " Client accounts.");

// Also ensure admin exists with admin123
$adminStmt = $pdo->prepare("SELECT id FROM users WHERE email = 'admin@cshub.com'");
$adminStmt->execute();
if (!$adminStmt->fetch()) {
    $pdo->prepare("INSERT INTO users (name, email, password, phone, role) VALUES ('Administrator', 'admin@cshub.com', ?, '60123456789', 'admin')")
        ->execute([password_hash('admin123', PASSWORD_DEFAULT)]);
    out("✓ Administrator account created (admin@cshub.com / admin123)");
} else {
    out("✓ Administrator account verified.");
}

// Fetch categories mapping: name -> id
$catRows = $pdo->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_KEY_PAIR);
$catMap = array_flip($catRows); // Name -> ID

// 4. Sample Services definition
$servicesData = [
    // Ahmad Kamil (Academic Tutoring)
    [
        'user_email'   => 'ahmad.kamil@cshub.com',
        'category'     => 'Academic Tutoring',
        'title'        => 'Calculus & Python Programming 1-on-1 Coaching',
        'description'  => "Patient step-by-step coaching for undergraduate Calculus I & II, Linear Algebra, and Python fundamentals. Includes past-year exam questions walkthrough, assignment debugging, and customized revision formula sheets. Sessions held on campus library discussion rooms or via Google Meet.",
        'price'        => 25.00,
        'availability' => 1,
    ],
    [
        'user_email'   => 'ahmad.kamil@cshub.com',
        'category'     => 'Academic Tutoring',
        'title'        => 'Data Structures & Java Lab Assignment Guidance',
        'description'  => "Assistance for Object-Oriented Programming (OOP), Linked Lists, Trees, Stacks, Queues, and Big-O algorithm analysis. Clear explanations to help you understand core concepts and pass your practical programming assessments with confidence.",
        'price'        => 30.00,
        'availability' => 1,
    ],

    // Nurul Hidayah (Graphic Design)
    [
        'user_email'   => 'nurul.hidayah@cshub.com',
        'category'     => 'Graphic Design',
        'title'        => 'Poster Design, Event Flyers & Social Media Carousels',
        'description'  => "Eye-catching, modern digital artwork tailored for student clubs, campus orientations, society conferences, and local business promotions. Delivered in high-res PNG, PDF print-ready format, and editable Canva/Photoshop source files within 24-48 hours.",
        'price'        => 35.00,
        'availability' => 1,
    ],
    [
        'user_email'   => 'nurul.hidayah@cshub.com',
        'category'     => 'Graphic Design',
        'title'        => 'Pitch Deck & Presentation Slide Makeover (PowerPoint / Canva)',
        'description'  => "Transform cluttered, text-heavy academic or startup slides into clean, visually engaging presentations. Includes custom infographics, consistent typography, clean layouts, and animated transitions to impress your lecturers or investors.",
        'price'        => 45.00,
        'availability' => 1,
    ],
    [
        'user_email'   => 'nurul.hidayah@cshub.com',
        'category'     => 'Graphic Design',
        'title'        => 'Minimalist Brand Logo & Identity Kit for Student Startups',
        'description'  => "Professional vector logo design including primary mark, secondary badge, curated color palette with HEX codes, typography pairing, and mockups. Ideal for emerging food vendors, apparel brands, and student tech ventures.",
        'price'        => 80.00,
        'availability' => 1,
    ],

    // Kevin Tan (Gadget Repair)
    [
        'user_email'   => 'kevin.tan@cshub.com',
        'category'     => 'Gadget Repair',
        'title'        => 'Laptop Reformatting, SSD Upgrade & Thermal Paste Servicing',
        'description'  => "Complete laptop tune-up: dust blowout, Arctic MX-4 premium thermal paste application for lower CPU/GPU temperatures, NVMe/SATA SSD installation, and clean OS reinstallation (Windows 10/11 or macOS). Free preliminary diagnosis via WhatsApp.",
        'price'        => 45.00,
        'availability' => 1,
    ],
    [
        'user_email'   => 'kevin.tan@cshub.com',
        'category'     => 'Gadget Repair',
        'title'        => 'Smartphone Screen & High-Capacity Battery Replacement',
        'description'  => "Fast on-campus diagnosis and repair for iPhone, Samsung, Xiaomi, and Realme models. Replacement of cracked OLED/LCD screens and degraded batteries with quality tested parts. Same-day turnaround available.",
        'price'        => 75.00,
        'availability' => 1,
    ],
    [
        'user_email'   => 'kevin.tan@cshub.com',
        'category'     => 'Gadget Repair',
        'title'        => 'Mechanical Keyboard Switch Lubing, Custom Cable & Deep Cleaning',
        'description'  => "Keyboard enthusiast service: Krytox 205g0 switch lubing, stabilizer holee mod & balancing, ultrasonic keycap wash, and custom coil cable assembly for a silent, thocky typing experience.",
        'price'        => 35.00,
        'availability' => 0, // Mark one as busy for variety
    ],

    // Hafiz Ramli (Delivery / Runner)
    [
        'user_email'   => 'hafiz.ramli@cshub.com',
        'category'     => 'Delivery / Runner',
        'title'        => 'Urgent Campus Parcel, Food & Document Dispatch Runner',
        'description'  => "Motorcycle dispatch service covering campus residential colleges (Mahallah), faculty offices, parcel collection lockers, and nearby food stalls. Direct door-to-door delivery with live WhatsApp location sharing.",
        'price'        => 6.00,
        'availability' => 1,
    ],
    [
        'user_email'   => 'hafiz.ramli@cshub.com',
        'category'     => 'Delivery / Runner',
        'title'        => 'Late-Night Printing, Colour Plotting & Spiral Binding Runner',
        'description'  => "Save time during final submissions! Send your PDF files via WhatsApp and I will handle high-quality digital printing, wire-o / comb binding, architectural plotting, and deliver the final copies directly to your faculty room.",
        'price'        => 12.00,
        'availability' => 1,
    ],

    // Farah Nabilah (Photography)
    [
        'user_email'   => 'farah.nabilah@cshub.com',
        'category'     => 'Photography',
        'title'        => 'Outdoor Graduation & Convocation Portrait Photoshoot (1 Hour)',
        'description'  => "Celebrate your milestone with aesthetic portraits shot on Sony A7III full-frame mirrorless. Includes 1-hour outdoor campus session, 20 color-graded high-resolution digital photos, full print rights, and fast cloud drive delivery in 48 hours.",
        'price'        => 120.00,
        'availability' => 1,
    ],
    [
        'user_email'   => 'farah.nabilah@cshub.com',
        'category'     => 'Photography',
        'title'        => 'Club Annual Dinner & Society Event Photography Package',
        'description'  => "Full event coverage for club inductions, gala dinners, sports tournaments, and award nights. Professional flash photography capturing stage presentations, VIP arrivals, candid crowd moments, and group portraits.",
        'price'        => 180.00,
        'availability' => 1,
    ],

    // Danial Lee (Computer Services)
    [
        'user_email'   => 'danial.lee@cshub.com',
        'category'     => 'Computer Services',
        'title'        => 'Hostel Wi-Fi Booster & Network Connectivity Troubleshooting',
        'description'  => "Fix slow internet, packet loss, DNS errors, and weak Wi-Fi signals in college dorm rooms. Setup and configuration of portable Wi-Fi repeaters, Ethernet cable crimping, and personal VPN guidance.",
        'price'        => 25.00,
        'availability' => 1,
    ],
    [
        'user_email'   => 'danial.lee@cshub.com',
        'category'     => 'Computer Services',
        'title'        => 'Complete Malware Removal, Windows 11 Optimization & Backup',
        'description'  => "Remove stubborn adware, crypto miners, spyware, and background bloatware slowing down your PC. Optimize startup programs, configure automatic Google Drive backups, and ensure your system is secure and responsive.",
        'price'        => 40.00,
        'availability' => 1,
    ],

    // Siti Sarah (Other)
    [
        'user_email'   => 'siti.sarah@cshub.com',
        'category'     => 'Other',
        'title'        => 'Resume, CV & Internship Cover Letter ATS Optimization',
        'description'  => "Professional review by a communications major with prior HR internship experience. Rewriting bullet points into impactful action-result statements, tailoring keywords to pass Applicant Tracking Systems (ATS), and fixing formatting errors.",
        'price'        => 20.00,
        'availability' => 1,
    ],
    [
        'user_email'   => 'siti.sarah@cshub.com',
        'category'     => 'Other',
        'title'        => 'Professional Badminton Racquet Restringing & Custom Grip Setup',
        'description'  => "Precision electronic tension restringing for badminton racquets (22 lbs to 30 lbs). Yonex BG66 Ultimax, Nanogy, and Aerobite string selections available with cushion wrap and tacky overgrip installation.",
        'price'        => 28.00,
        'availability' => 1,
    ],

    // Priyadarshini Mohan (Academic Tutoring)
    [
        'user_email'   => 'priya.mohan@cshub.com',
        'category'     => 'Academic Tutoring',
        'title'        => 'Engineering Statics, Dynamics & Fluid Mechanics Problem-Solving',
        'description'  => "Intensive problem-solving sessions for Mechanical and Civil Engineering students. Mastery of free-body diagrams, shear force & bending moment diagrams, kinematics, and Bernoulli equation applications.",
        'price'        => 35.00,
        'availability' => 1,
    ],

    // Ayman Aqasyah (Computer Services / Web)
    [
        'user_email'   => 'aymanaqasyah1@gmail.com',
        'category'     => 'Computer Services',
        'title'        => 'Full-Stack Web App Development & Bug Fixing (PHP, MySQL, JS)',
        'description'  => "Hands-on assistance for web development assignments and personal projects. Responsive UI styling, database normalization, PHP authentication, RESTful APIs, and local development environment (Laragon / XAMPP) troubleshooting.",
        'price'        => 60.00,
        'availability' => 1,
    ],
    [
        'user_email'   => 'aymanaqasyah1@gmail.com',
        'category'     => 'Computer Services',
        'title'        => 'Final Year Project (FYP) Architecture & Database Design Review',
        'description'  => "1-on-1 consultation for software engineering final year projects. Review of entity relationship diagrams (ERD), normalized schema design, security best practices (SQL injection prevention, password hashing), and code refactoring.",
        'price'        => 50.00,
        'availability' => 1,
    ]
];

// Clean existing services & reviews to avoid duplicate cascades
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$pdo->exec("TRUNCATE TABLE reviews");
$pdo->exec("TRUNCATE TABLE services");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

$serviceIdMap = []; // title -> id

$insertServiceStmt = $pdo->prepare("
    INSERT INTO services (user_id, category_id, title, description, price, availability, created_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");

foreach ($servicesData as $s) {
    $userId = $userMap[$s['user_email']] ?? null;
    $catId = $catMap[$s['category']] ?? null;

    if ($userId && $catId) {
        $insertServiceStmt->execute([
            $userId,
            $catId,
            $s['title'],
            $s['description'],
            $s['price'],
            $s['availability']
        ]);
        $sId = $pdo->lastInsertId();
        $serviceIdMap[$s['title']] = $sId;
    }
}

out("✓ Inserted " . count($serviceIdMap) . " detailed Services across all 7 Categories.");

// 5. Authentic Client Reviews
$reviewsData = [
    [
        'service_title' => 'Calculus & Python Programming 1-on-1 Coaching',
        'client_email'  => 'siti.aisyah@gmail.com',
        'rating'        => 5,
        'review_text'   => "Ahmad is an incredible tutor! Needed urgent peer tutoring right before my Calculus II midterm. His explanations of integration techniques and Python loops were so clear. Aced the exam with an A-!"
    ],
    [
        'service_title' => 'Calculus & Python Programming 1-on-1 Coaching',
        'client_email'  => 'adam.farhan@gmail.com',
        'rating'        => 5,
        'review_text'   => "Very patient tutor. Helped me debug my Python semester project and taught me how to structure clean functions. Highly recommended for beginners."
    ],
    [
        'service_title' => 'Poster Design, Event Flyers & Social Media Carousels',
        'client_email'  => 'chloe.tan@gmail.com',
        'rating'        => 5,
        'review_text'   => "Nurul delivered our club orientation banner and Instagram carousel in less than 24 hours. The aesthetics were top notch and everyone loved the visuals!"
    ],
    [
        'service_title' => 'Pitch Deck & Presentation Slide Makeover (PowerPoint / Canva)',
        'client_email'  => 'bryan.lim@gmail.com',
        'rating'        => 5,
        'review_text'   => "Turned our boring 30-slide presentation into a slick, modern investor pitch deck. Our lecturer praised the design in front of the whole lecture hall."
    ],
    [
        'service_title' => 'Laptop Reformatting, SSD Upgrade & Thermal Paste Servicing',
        'client_email'  => 'marcus.wong@gmail.com',
        'rating'        => 5,
        'review_text'   => "My laptop suddenly blue-screened right before assignment submission. Kevin fixed the operating system and cleaned out the thermal paste in under 3 hours. Absolute lifesaver!"
    ],
    [
        'service_title' => 'Smartphone Screen & High-Capacity Battery Replacement',
        'client_email'  => 'haziq.izzat@gmail.com',
        'rating'        => 5,
        'review_text'   => "Fixed my cracked iPhone screen on campus during lunch hour. Tested touch responsiveness and TrueTone works perfectly. Much cheaper than official service centers."
    ],
    [
        'service_title' => 'Urgent Campus Parcel, Food & Document Dispatch Runner',
        'client_email'  => 'aina.syafiah@gmail.com',
        'rating'        => 5,
        'review_text'   => "Fast and reliable runner! Delivered my heavy package from the main campus courier station straight to my residential college door in the rain without any issues."
    ],
    [
        'service_title' => 'Late-Night Printing, Colour Plotting & Spiral Binding Runner',
        'client_email'  => 'aliahmad@gmail.com',
        'rating'        => 5,
        'review_text'   => "Saved me from failing our group design submission deadline at 2 AM. Printed full color architectural drawings and bound them neatly."
    ],
    [
        'service_title' => 'Outdoor Graduation & Convocation Portrait Photoshoot (1 Hour)',
        'client_email'  => 'bryan.lim@gmail.com',
        'rating'        => 5,
        'review_text'   => "Farah has an amazing eye for lighting, natural poses, and campus backdrop spots. The graduation photos turned out magazine-quality!"
    ],
    [
        'service_title' => 'Club Annual Dinner & Society Event Photography Package',
        'client_email'  => 'chloe.tan@gmail.com',
        'rating'        => 5,
        'review_text'   => "Covered our Computer Science Society annual grand dinner. Captures every emotion, stage award, and group photo with crystal clear sharpness."
    ],
    [
        'service_title' => 'Hostel Wi-Fi Booster & Network Connectivity Troubleshooting',
        'client_email'  => 'adam.farhan@gmail.com',
        'rating'        => 4,
        'review_text'   => "Danial identified why our block had terrible Wi-Fi dead spots and configured a repeater for our room. Ping dropped from 180ms to 24ms."
    ],
    [
        'service_title' => 'Complete Malware Removal, Windows 11 Optimization & Backup',
        'client_email'  => 'siti.aisyah@gmail.com',
        'rating'        => 5,
        'review_text'   => "Removed suspicious adware popups that were hijacking Chrome. My laptop boots in 8 seconds now. Very friendly and knowledgeable!"
    ],
    [
        'service_title' => 'Resume, CV & Internship Cover Letter ATS Optimization',
        'client_email'  => 'haziq.izzat@gmail.com',
        'rating'        => 5,
        'review_text'   => "Siti restructured my CV to pass automated ATS screening. Received two internship interview invitations within a week of applying!"
    ],
    [
        'service_title' => 'Professional Badminton Racquet Restringing & Custom Grip Setup',
        'client_email'  => 'marcus.wong@gmail.com',
        'rating'        => 5,
        'review_text'   => "Restrung my Yonex Astrox at 27 lbs with BG66 Ultimax. Crisp sound and fantastic tension retention during our campus friendly match."
    ],
    [
        'service_title' => 'Engineering Statics, Dynamics & Fluid Mechanics Problem-Solving',
        'client_email'  => 'aliahmad@gmail.com',
        'rating'        => 5,
        'review_text'   => "Priya broke down 3D moment calculations and shear stress distribution into simple diagrams. Passed my engineering quiz with flying colors!"
    ],
    [
        'service_title' => 'Full-Stack Web App Development & Bug Fixing (PHP, MySQL, JS)',
        'client_email'  => 'adam.farhan@gmail.com',
        'rating'        => 5,
        'review_text'   => "Ayman resolved our PHP session fixation and PDO query bugs within an hour. Excellent explanations and clean code practices."
    ],
    [
        'service_title' => 'Final Year Project (FYP) Architecture & Database Design Review',
        'client_email'  => 'marcus.wong@gmail.com',
        'rating'        => 5,
        'review_text'   => "The database schema review prevented major redundancy errors before our proposal defense. Got great feedback from our academic supervisor!"
    ]
];

$insertReviewStmt = $pdo->prepare("
    INSERT INTO reviews (service_id, client_id, rating, review_text, created_at)
    VALUES (?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? DAY))
");

$daysAgo = 1;
foreach ($reviewsData as $r) {
    $serviceId = $serviceIdMap[$r['service_title']] ?? null;
    $clientId = $userMap[$r['client_email']] ?? null;

    if ($serviceId && $clientId) {
        $insertReviewStmt->execute([
            $serviceId,
            $clientId,
            $r['rating'],
            $r['review_text'],
            $daysAgo++
        ]);
    }
}

out("✓ Inserted " . count($reviewsData) . " authentic Peer Reviews with ratings.");

out("\n=======================================================");
out("🎉 DATABASE SEEDING COMPLETED SUCCESSFULLY!");
out("=======================================================");
out("Total Users: " . $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn());
out("Total Freelancers: " . $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'freelancer'")->fetchColumn());
out("Total Clients: " . $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn());
out("Total Services: " . $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn());
out("Total Reviews: " . $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn());
out("All sample passwords: 'password123'");
out("Admin account: admin@cshub.com / admin123");

if (!$isCli) {
    echo "<p><a href='/cshub/index.php' style='color:#38bdf8; font-weight:bold;'>&larr; Return to ProVenture Homepage</a></p>";
    echo "</body></html>";
}
