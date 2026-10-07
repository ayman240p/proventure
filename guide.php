<?php
// guide.php - ProVenture Interactive Buyer Guides Directory & Detail Page
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Buyer Guides - ProVenture';

$guides = [
    'plumber' => [
        'title' => 'Guide to Hiring a Reliable Plumber',
        'category' => 'Plumbing & Sanitary',
        'icon' => 'bi-wrench-adjustable',
        'intro' => 'Plumbing emergencies and pipe issues require swift action, but hiring without verification can lead to recurring leaks and property damage. Use this checklist to verify your plumber before work begins.',
        'checklist' => [
            [
                'title' => 'Verify Plumbing Credentials & Experience',
                'desc' => 'Ask about past experience with your specific issue — whether it involves concealed pipe leaks, high-pressure water pumps, roof gutters, or sewage unclogging.'
            ],
            [
                'title' => 'Clarify Scope & Emergency Call-Out Fees',
                'desc' => 'Confirm upfront if there is an inspection, transport, or after-hours surcharge before the plumber travels to your location.'
            ],
            [
                'title' => 'Obtain an Itemized Quote (Parts vs. Labor)',
                'desc' => 'Request a transparent breakdown separating labor charges from replacement parts (pipes, ball valves, rubber seals, or faucets).'
            ],
            [
                'title' => 'Check Reviews & Workmanship Warranty',
                'desc' => 'Read real client reviews on ProVenture and confirm at least a 14 to 30-day warranty against recurring leaks on completed joints.'
            ]
        ],
        'ctaText' => 'Find Plumbers on ProVenture',
        'query' => 'Plumber'
    ],
    'aircon' => [
        'title' => 'Guide to Hiring an Aircon Technician',
        'category' => 'Cooling & Appliances',
        'icon' => 'bi-snow',
        'intro' => 'Regular air conditioner servicing maintains energy efficiency and air quality. Avoid overpaying for unnecessary chemical overhauls or gas top-ups with these verification steps.',
        'checklist' => [
            [
                'title' => 'Distinguish Normal Servicing from Chemical Overhauls',
                'desc' => 'Standard routine cleaning (filters, fan blower, faceplate) is sufficient every 3–4 months. Full chemical dismantle is only needed for severe mold, odors, or deep blockages.'
            ],
            [
                'title' => 'Require Pressure Testing Before Adding Gas',
                'desc' => 'Modern air conditioning systems (R32 / R410A) are sealed closed-loops. Ask the technician to check pressure gauges for physical leaks before agreeing to gas refills.'
            ],
            [
                'title' => 'Inspect Drainage Line & Water Tray Flushing',
                'desc' => 'Ensure the technician vacuums or pressure-flushes the drainage line to prevent interior water dripping and condensate tray overflow.'
            ],
            [
                'title' => 'Request a Cooling Performance Warranty',
                'desc' => 'Ask for a 30-day guarantee covering water leaks, cooling temperature, and workmanship post-servicing.'
            ]
        ],
        'ctaText' => 'Find Aircon Technicians on ProVenture',
        'query' => 'Aircon'
    ],
    'catering' => [
        'title' => 'Guide to Hiring Event Catering',
        'category' => 'Events & Hospitality',
        'icon' => 'bi-cup-hot-fill',
        'intro' => 'Great food makes an unforgettable event. Ensure punctual setup, generous portions, and food safety compliance by ticking off these essential catering criteria.',
        'checklist' => [
            [
                'title' => 'Confirm Guest Headcount & Dietary Guidelines',
                'desc' => 'Verify Halal certification, vegetarian or vegan requirements, and common allergies (peanuts, shellfish). Always budget a 5–10% portion buffer for unexpected guests.'
            ],
            [
                'title' => 'Clarify Equipment, Cutlery & Cleanup Inclusions',
                'desc' => 'Confirm whether the package includes chafing dishes, food warmers, serving utensils, disposable cutlery, napkins, and trash disposal bags.'
            ],
            [
                'title' => 'Lock Down Arrival & Setup Timetable',
                'desc' => 'Require the catering crew to arrive at least 60 to 90 minutes before your guests arrive to allow food to warm and presentation to be perfected.'
            ],
            [
                'title' => 'Review Real Event Photos & Food Portions',
                'desc' => 'Inspect real past buffet setup photos on ProVenture to assess presentation cleanliness, portion sizes, and food freshness.'
            ]
        ],
        'ctaText' => 'Find Event Catering on ProVenture',
        'query' => 'Catering'
    ],
    'renovation' => [
        'title' => 'Guide to Hiring a Renovation Contractor',
        'category' => 'Home Improvement',
        'icon' => 'bi-hammer',
        'intro' => 'Home renovations involve significant time and capital. Protect your budget and property by following disciplined contractor vetting, clear contracts, and staged payments.',
        'checklist' => [
            [
                'title' => 'Verify Business Registration & CIDB / SSM Records',
                'desc' => 'Confirm the contractor operates a registered business and request verifiable references or recent project walkthroughs in your area.'
            ],
            [
                'title' => 'Insist on a Written Bill of Quantities (BOQ)',
                'desc' => 'Avoid broad lump-sum quotes. Ensure every item (tile specifications, paint brands, hacking dimensions, electrical points) is clearly itemized.'
            ],
            [
                'title' => 'Structure Milestone-Based Progressive Payments',
                'desc' => 'Never pay 100% upfront. Structure payments around verified completion stages (e.g. 15% deposit, 30% after wet works, 30% after carpentry, 25% on handover).'
            ],
            [
                'title' => 'Establish a Written Defect Liability Period (DLP)',
                'desc' => 'Obtain a 3 to 6-month warranty where the contractor is contractually bound to rectify plaster cracks, hollow tiles, or paint defects at no added cost.'
            ]
        ],
        'ctaText' => 'Find Renovation Pros on ProVenture',
        'query' => 'Renovation'
    ],
    'handyman' => [
        'title' => 'Guide to Hiring a Handyman',
        'category' => 'General Repairs',
        'icon' => 'bi-tools',
        'intro' => 'A versatile handyman can fix multiple minor household annoyances in a single trip. Maximize efficiency and value with this preparation checklist.',
        'checklist' => [
            [
                'title' => 'Group Tasks into a Single Punch-List',
                'desc' => 'Combine drilling, curtain rod hanging, door hinge lubrication, silicone resealing, and furniture assembly into one visit to get the best value.'
            ],
            [
                'title' => 'Share Clear Photos & Measurements on WhatsApp',
                'desc' => 'Send clear pictures of mounting surfaces (concrete, drywall, tiles) and issues beforehand so the handyman brings the correct drills, anchors, and fasteners.'
            ],
            [
                'title' => 'Clarify Who Supplies Parts & Consumables',
                'desc' => 'Confirm whether replacement screws, wall plugs, replacement bulbs, or caulking are included in the price or billed separately.'
            ],
            [
                'title' => 'Agree on Flat Rate vs. Hourly Pricing',
                'desc' => 'Agree on the pricing structure upfront before work commences to avoid unexpected charges if an assembly task takes longer than estimated.'
            ]
        ],
        'ctaText' => 'Find Handymen on ProVenture',
        'query' => 'Handyman'
    ],
    'tutor' => [
        'title' => 'Guide to Hiring a Home Tutor',
        'category' => 'Academic & Tutoring',
        'icon' => 'bi-mortarboard-fill',
        'intro' => 'The right private tutor inspires confidence and academic excellence. Ensure your student receives tailored instruction by assessing credentials and teaching style.',
        'checklist' => [
            [
                'title' => 'Verify Academic Credentials & Syllabus Familiarity',
                'desc' => 'Confirm the tutor has proven mastery of the target curriculum (KSSR, SPM, IGCSE, A-Levels, Coding, or Languages) and recent syllabus updates.'
            ],
            [
                'title' => 'Schedule a Paid Trial Session First',
                'desc' => 'Book an initial trial lesson to observe the tutor’s patience, explanation clarity, and rapport with the student before committing to monthly arrangements.'
            ],
            [
                'title' => 'Define Learning Milestones & Progress Reviews',
                'desc' => 'Set concrete objectives (exam preparation, weak subject remediation, homework guidance) and agree on monthly progress feedback.'
            ],
            [
                'title' => 'Clarify Rates, Rescheduling & Cancellation Rules',
                'desc' => 'Confirm hourly rates, payment schedules, and notice required if a lesson needs to be rescheduled due to sickness or school activities.'
            ]
        ],
        'ctaText' => 'Find Home Tutors on ProVenture',
        'query' => 'Tutor'
    ],
    'learn' => [
        'title' => 'How to Create a Good Listing to Attract More Clients',
        'category' => 'Freelancer Playbook',
        'icon' => 'bi-rocket-takeoff-fill',
        'intro' => 'A well-crafted service listing builds instant trust, ranks higher on ProVenture search, and turns casual visitors into direct WhatsApp inquiries. Follow these 5 proven steps to maximize your bookings.',
        'checklist' => [
            [
                'title' => 'Write a Specific, Action-Oriented Title',
                'desc' => 'Avoid vague titles like "I do design". Use clear, searchable keywords stating what you deliver and who it is for (e.g. "Modern Vector Logo Design & Brand Identity Package").'
            ],
            [
                'title' => 'Upload High-Resolution Portfolio Samples',
                'desc' => 'Real photos of past work, before-and-after comparisons, or clean mockups attract 3.5x more inquiries. Avoid generic, watermarked stock imagery.'
            ],
            [
                'title' => 'Be Completely Transparent with Starting Rates',
                'desc' => 'State your starting or hourly price upfront. Clients avoid listings that say "contact for price" without a baseline. Clearly define what is covered in your base package.'
            ],
            [
                'title' => 'Structure Your Description with Bullet Points',
                'desc' => 'Clearly break down: 1) What is included in the service, 2) Information needed from the client, 3) Estimated completion time, and 4) Revision terms.'
            ],
            [
                'title' => 'Respond Rapidly & Professionally on WhatsApp',
                'desc' => 'Replies within 15–30 minutes dramatically boost booking rates. Prepare a polite welcome message and quick intake questionnaire ready to send when clients reach out.'
            ]
        ],
        'ctaText' => 'Post a Service Listing Now',
        'ctaUrl' => '/cshub/freelancer/add-service.php',
        'query' => ''
    ]
];

$requestedType = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : '';
$currentGuide = $guides[$requestedType] ?? null;

if ($currentGuide) {
    $pageTitle = $currentGuide['title'] . ' - ProVenture';
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/cshub/index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="/cshub/guide.php" class="text-decoration-none">Buyer Guides</a></li>
            <?php if ($currentGuide): ?>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($currentGuide['title']) ?></li>
            <?php else: ?>
                <li class="breadcrumb-item active" aria-current="page">All Guides</li>
            <?php endif; ?>
        </ol>
    </nav>

    <?php if ($currentGuide): ?>
        <!-- Single Detailed Guide View -->
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card rounded-4 shadow-sm border overflow-hidden mb-5">
                    <div class="card-header border-bottom py-4 px-4 px-md-5" style="background: rgba(18, 140, 126, 0.05);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="guide-modal-icon-badge rounded-3 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi <?= htmlspecialchars($currentGuide['icon']) ?> fs-3"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                        <?= htmlspecialchars($currentGuide['category']) ?>
                                    </span>
                                    <span class="badge bg-success-subtle text-success fw-semibold" style="font-size: 0.72rem;">Verified Tips</span>
                                </div>
                                <h2 class="h3 fw-bold mb-0 text-primary"><?= htmlspecialchars($currentGuide['title']) ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <div class="guide-intro-box p-3 rounded-3 mb-4" style="background: rgba(18, 140, 126, 0.06); border-left: 4px solid var(--primary-color, #128C7E);">
                            <p class="mb-0 text-secondary"><?= htmlspecialchars($currentGuide['intro']) ?></p>
                        </div>

                        <h5 class="fw-bold text-uppercase mb-4 d-flex align-items-center gap-2" style="font-size: 0.85rem; letter-spacing: 0.08em; color: var(--text-primary, #0f172a);">
                            <i class="bi bi-check2-circle text-primary fs-5"></i> Key Hiring Checklist
                        </h5>

                        <div class="d-flex flex-column gap-3 mb-4">
                            <?php foreach ($currentGuide['checklist'] as $idx => $item): ?>
                                <div class="guide-checklist-item d-flex gap-3 align-items-start">
                                    <span class="guide-step-num"><?= $idx + 1 ?></span>
                                    <div class="flex-grow-1">
                                        <strong class="d-block mb-1 text-dark dark-text-light"><?= htmlspecialchars($item['title']) ?></strong>
                                        <span class="text-secondary small d-block"><?= htmlspecialchars($item['desc']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="p-3 rounded-3 mb-4 d-flex align-items-center gap-3 border" style="background: rgba(37, 211, 102, 0.06);">
                            <i class="bi bi-shield-check text-success fs-2 flex-shrink-0"></i>
                            <div class="small text-muted">
                                <strong>Safety Reminder:</strong> ProVenture connects people directly without holding payments. Always confirm scope of work on WhatsApp and avoid full upfront transfers before inspecting proof of progress.
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-between align-items-sm-center pt-3 border-top">
                            <a href="/cshub/guide.php" class="btn btn-outline-secondary rounded-pill px-4">
                                <i class="bi bi-arrow-left me-1"></i> All Guides
                            </a>
                            <?php 
                            $ctaHref = !empty($currentGuide['ctaUrl']) ? $currentGuide['ctaUrl'] : ('/cshub/search.php?q=' . urlencode($currentGuide['query']));
                            ?>
                            <a href="<?= htmlspecialchars($ctaHref) ?>" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
                                <span><?= htmlspecialchars($currentGuide['ctaText']) ?></span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- All Guides Grid View -->
        <div class="text-center mb-5">
            <h1 class="fw-bold mb-2">ProVenture Buyer Guides</h1>
            <p class="text-muted mx-auto" style="max-width: 620px;">
                Expert checklists and practical tips to help you hire local talent with confidence, transparent pricing, and zero middleman markups.
            </p>
        </div>

        <div class="row g-4">
            <?php foreach ($guides as $key => $guide): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 rounded-4 shadow-sm border service-card p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="guide-modal-icon-badge rounded-3 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                    <i class="bi <?= htmlspecialchars($guide['icon']) ?> fs-4"></i>
                                </div>
                                <div>
                                    <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase" style="font-size: 0.68rem;">
                                        <?= htmlspecialchars($guide['category']) ?>
                                    </span>
                                    <h5 class="fw-bold mb-0 mt-1" style="font-size: 1.05rem;">
                                        <?= htmlspecialchars($guide['title']) ?>
                                    </h5>
                                </div>
                            </div>
                            <p class="text-muted small mb-4" style="line-height: 1.6;">
                                <?= htmlspecialchars(substr($guide['intro'], 0, 130)) ?>...
                            </p>
                        </div>
                        <div class="d-flex gap-2 pt-3 border-top">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill flex-grow-1" data-bs-toggle="modal" data-bs-target="#buyerGuideModal" data-guide="<?= htmlspecialchars($key) ?>">
                                <i class="bi bi-eye me-1"></i> Quick Modal
                            </button>
                            <a href="/cshub/guide.php?type=<?= htmlspecialchars($key) ?>" class="btn btn-sm btn-primary rounded-pill px-3">
                                Full Guide <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
