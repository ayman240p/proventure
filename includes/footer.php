<?php
$jsScriptVer = file_exists(__DIR__ . '/../assets/js/script.js') ? filemtime(__DIR__ . '/../assets/js/script.js') : time();
$jsThemeVer = file_exists(__DIR__ . '/../assets/js/theme.js') ? filemtime(__DIR__ . '/../assets/js/theme.js') : time();
?>
</main>

<!-- ==========================================================
     Responsive Modern Footer Component
     ========================================================== -->
<footer class="site-footer mt-auto">
    <!-- 1. Top Notice Banner -->
    <div class="footer-notice-banner">
        <div class="container text-center">
            <p class="footer-notice-text mb-0">
                <strong>Heads up:</strong> ProVenture connects people &mdash; we don't hold or process any payments. Verify before you transfer.
                <a href="#safeDealingModal" data-bs-toggle="modal" class="safe-dealing-link ms-1">Safe dealing tips &rarr;</a>
            </p>
        </div>
    </div>

    <!-- 2. Main Footer Columns -->
    <div class="footer-main-wrap">
        <div class="container">
            <div class="row g-4 justify-content-between">
                <!-- Column 1 (Brand) -->
                <div class="col-12 col-lg-3 col-md-12 mb-2 mb-lg-0">
                    <a class="footer-brand" href="/cshub/index.php">
                        <span class="footer-brand-badge">
                            <i class="bi bi-briefcase-fill"></i>
                        </span>
                        <span class="footer-brand-name">ProVenture</span>
                    </a>
                    <p class="footer-tagline">
                        Local talent. Direct contact. No middleman cut.
                    </p>
                </div>

                <!-- Column 2 (BROWSE) -->
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <h6 class="footer-heading">Browse</h6>
                    <ul class="footer-links">
                        <li><a href="/cshub/search.php">All Services</a></li>
                        <li><a href="/cshub/business.php">For business</a></li>
                    </ul>
                </div>

                <!-- Column 3 (FREELANCERS) -->
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <h6 class="footer-heading">Freelancers</h6>
                    <ul class="footer-links">
                        <li><a href="/cshub/search.php">Find gig work</a></li>
                        <li><a href="/cshub/freelancer/add-service.php">Post a gig</a></li>
                        <li><a href="#buyerGuideModal" data-bs-toggle="modal" data-guide="learn">Learn</a></li>
                        <li><a href="/cshub/client/dashboard.php">Dashboard</a></li>
                    </ul>
                </div>

                <!-- Column 4 (TRUST & SAFETY) -->
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <h6 class="footer-heading">Trust &amp; Safety</h6>
                    <ul class="footer-links">
                        <li><a href="#safeDealingModal" data-bs-toggle="modal">Safe Dealing Tips</a></li>
                        <li><a href="#privacyPolicyModal" data-bs-toggle="modal">Privacy Policy</a></li>
                        <li><a href="#termsModal" data-bs-toggle="modal">Terms of Service</a></li>
                    </ul>
                </div>

                <!-- Column 5 (BUYER GUIDES) -->
                <div class="col-6 col-sm-6 col-md-3 col-lg-3">
                    <h6 class="footer-heading">Buyer Guides</h6>
                    <ul class="footer-links">
                        <li><a href="#buyerGuideModal" data-bs-toggle="modal" data-guide="plumber">Hire a Plumber</a></li>
                        <li><a href="#buyerGuideModal" data-bs-toggle="modal" data-guide="aircon">Hire an Aircon Technician</a></li>
                        <li><a href="#buyerGuideModal" data-bs-toggle="modal" data-guide="catering">Hire Event Catering</a></li>
                        <li><a href="#buyerGuideModal" data-bs-toggle="modal" data-guide="renovation">Hire a Renovation Contractor</a></li>
                        <li><a href="#buyerGuideModal" data-bs-toggle="modal" data-guide="handyman">Hire a Handyman</a></li>
                        <li><a href="#buyerGuideModal" data-bs-toggle="modal" data-guide="tutor">Hire a Home Tutor</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Bottom Center Section -->
    <div class="footer-bottom-wrap">
        <div class="container text-center">
            <div class="footer-follow-title">Follow Us</div>
            <div class="footer-social-group">
                <a href="https://www.tiktok.com" target="_blank" rel="noopener noreferrer" class="footer-social-btn social-tiktok" aria-label="TikTok" title="TikTok">
                    <i class="bi bi-tiktok"></i>
                </a>
                <a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" class="footer-social-btn social-instagram" aria-label="Instagram" title="Instagram">
                    <i class="bi bi-instagram"></i>
                </a>
                <a href="https://www.facebook.com" target="_blank" rel="noopener noreferrer" class="footer-social-btn social-facebook" aria-label="Facebook" title="Facebook">
                    <i class="bi bi-facebook"></i>
                </a>
                <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" class="footer-social-btn social-twitter" aria-label="X (Twitter)" title="X (Twitter)">
                    <i class="bi bi-twitter-x"></i>
                </a>
            </div>
            <div class="footer-copyright">
                &copy; 2026 ProVenture. All rights reserved.
            </div>
            <div class="footer-subtext">
                Handled by ProVenture Enterprise
            </div>
        </div>
    </div>
</footer>

<!-- Safe Dealing Tips Modal -->
<div class="modal fade" id="safeDealingModal" tabindex="-1" aria-labelledby="safeDealingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow border">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-primary d-flex align-items-center gap-2" id="safeDealingModalLabel">
                    <i class="bi bi-shield-check text-success fs-4"></i> Safe Dealing Tips
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex gap-3 align-items-start">
                        <span class="badge bg-primary-subtle text-primary rounded-circle p-2 mt-1"><i class="bi bi-person-check-fill"></i></span>
                        <div>
                            <strong class="d-block mb-1">Verify Identity &amp; Reviews</strong>
                            <span class="text-muted small">Always check the freelancer's profile, ratings, and past client reviews on ProVenture before engaging.</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3 align-items-start">
                        <span class="badge bg-success-subtle text-success rounded-circle p-2 mt-1"><i class="bi bi-chat-dots-fill"></i></span>
                        <div>
                            <strong class="d-block mb-1">Clear Scope on WhatsApp</strong>
                            <span class="text-muted small">Clearly agree on scope of work, timeline, deliverables, and exact pricing before work begins.</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3 align-items-start">
                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-circle p-2 mt-1"><i class="bi bi-wallet2"></i></span>
                        <div>
                            <strong class="d-block mb-1">Avoid Full Upfront Transfers</strong>
                            <span class="text-muted small">For medium/large gigs, use milestone payments or small deposits. Never transfer 100% upfront without verified milestones.</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3 align-items-start">
                        <span class="badge bg-danger-subtle text-danger rounded-circle p-2 mt-1"><i class="bi bi-exclamation-triangle-fill"></i></span>
                        <div>
                            <strong class="d-block mb-1">Watch Out For Scams</strong>
                            <span class="text-muted small">ProVenture staff will never ask for your online banking OTP, passwords, or gift card codes.</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">Got it, thanks!</button>
            </div>
        </div>
    </div>
</div>

<!-- Privacy Policy Modal -->
<div class="modal fade" id="privacyPolicyModal" tabindex="-1" aria-labelledby="privacyPolicyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow border">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-primary d-flex align-items-center gap-2" id="privacyPolicyModalLabel">
                    <i class="bi bi-lock-fill text-primary fs-4"></i> Privacy Policy
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="text-muted small mb-3">At ProVenture, we are committed to safeguarding your personal data in accordance with the Personal Data Protection Act (PDPA).</p>
                <ul class="text-muted small ps-3 mb-0 d-flex flex-column gap-2">
                    <li><strong>Data Collected:</strong> Name, WhatsApp contact number, email address, and service listings you provide.</li>
                    <li><strong>Purpose:</strong> Connecting local freelancers with clients. We never sell your personal data to third parties.</li>
                    <li><strong>Security:</strong> All account credentials are protected with encrypted password hashing and security protocols.</li>
                </ul>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Terms of Service Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow border">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-primary d-flex align-items-center gap-2" id="termsModalLabel">
                    <i class="bi bi-file-text-fill text-primary fs-4"></i> Terms of Service
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="text-muted small mb-3">By accessing or using ProVenture, you agree to comply with the following platform guidelines:</p>
                <ul class="text-muted small ps-3 mb-0 d-flex flex-column gap-2">
                    <li><strong>Direct Connections:</strong> ProVenture is a discovery directory connecting community freelancers directly with buyers. We do not hold escrow or process payments.</li>
                    <li><strong>Service Standards:</strong> Freelancers must list lawful, authentic services. Prohibited or deceptive listings are subject to immediate removal.</li>
                    <li><strong>Direct Contracts:</strong> All payment transactions and deliverables are agreed directly between client and freelancer.</li>
                </ul>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================
     Interactive Buyer Guide Modal
     ========================================================== -->
<div class="modal fade" id="buyerGuideModal" tabindex="-1" aria-labelledby="buyerGuideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 shadow-lg border overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header border-bottom py-3 px-4" style="background: rgba(18, 140, 126, 0.05);">
                <div class="d-flex align-items-center gap-3">
                    <div id="guideModalIconWrap" class="guide-modal-icon-badge rounded-3 bg-primary text-white d-flex align-items-center justify-content-center">
                        <i class="bi bi-wrench-adjustable fs-4" id="guideModalIcon"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase" id="guideModalCategory" style="font-size: 0.7rem; letter-spacing: 0.05em;">Plumbing &amp; Sanitary</span>
                            <span class="badge bg-success-subtle text-success fw-semibold" style="font-size: 0.7rem;">Verified Checklist</span>
                        </div>
                        <h5 class="modal-title fw-bold mb-0 text-primary" id="buyerGuideModalLabel">Guide to Hiring a Reliable Plumber</h5>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <div class="guide-intro-box p-3 rounded-3 mb-4" id="guideModalIntroBox" style="background: rgba(18, 140, 126, 0.06); border-left: 4px solid var(--primary-color, #128C7E);">
                    <p class="mb-0 text-secondary small" id="guideModalIntro">
                        Plumbing emergencies and pipe issues require swift action, but hiring without verification can lead to recurring leaks and property damage. Use this checklist to verify your plumber before work begins.
                    </p>
                </div>

                <h6 class="fw-bold text-uppercase mb-3 d-flex align-items-center gap-2" style="font-size: 0.78rem; letter-spacing: 0.08em; color: var(--text-primary, #0f172a);">
                    <i class="bi bi-check2-circle text-primary"></i> Key Hiring Checklist
                </h6>

                <div class="guide-checklist-list d-flex flex-column gap-3" id="guideModalChecklist">
                    <!-- Dynamic Checklist items loaded by script.js -->
                    <div class="guide-checklist-item d-flex gap-3 align-items-start">
                        <span class="guide-step-num">1</span>
                        <div class="flex-grow-1">
                            <strong class="d-block mb-1 text-dark dark-text-light">Verify Plumbing Credentials &amp; Experience</strong>
                            <span class="text-secondary small d-block">Ask about their experience with your specific issue (e.g. concealed pipe leaks, high-pressure pumps, roof gutters, or drain clearing).</span>
                        </div>
                    </div>
                    <div class="guide-checklist-item d-flex gap-3 align-items-start">
                        <span class="guide-step-num">2</span>
                        <div class="flex-grow-1">
                            <strong class="d-block mb-1 text-dark dark-text-light">Clarify Scope &amp; Emergency Call-Out Fees</strong>
                            <span class="text-secondary small d-block">Confirm if there is an inspection, transport, or after-hours surcharge before the plumber travels to your location.</span>
                        </div>
                    </div>
                    <div class="guide-checklist-item d-flex gap-3 align-items-start">
                        <span class="guide-step-num">3</span>
                        <div class="flex-grow-1">
                            <strong class="d-block mb-1 text-dark dark-text-light">Obtain an Itemized Quote (Parts vs. Labor)</strong>
                            <span class="text-secondary small d-block">Request a transparent breakdown separating labor charges from replacement parts (pipes, ball valves, rubber seals, or faucets).</span>
                        </div>
                    </div>
                    <div class="guide-checklist-item d-flex gap-3 align-items-start">
                        <span class="guide-step-num">4</span>
                        <div class="flex-grow-1">
                            <strong class="d-block mb-1 text-dark dark-text-light">Check Reviews &amp; Workmanship Warranty</strong>
                            <span class="text-secondary small d-block">Read real client feedback on ProVenture and confirm at least a 14 to 30-day warranty against recurring leaks.</span>
                        </div>
                    </div>
                </div>

                <div class="p-3 rounded-3 mt-4 d-flex align-items-center gap-3 border" style="background: rgba(37, 211, 102, 0.06);">
                    <i class="bi bi-shield-check text-success fs-3 flex-shrink-0"></i>
                    <div class="small text-muted">
                        <strong>Direct Communication:</strong> Connect directly on WhatsApp without platform commissions. Always request an upfront written summary of the job before payment.
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top px-4 py-3 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                <a href="/cshub/search.php?q=Plumber" id="guideModalCta" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
                    <span id="guideModalCtaText">Find Plumbers on ProVenture</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/report_modal.php'; ?>
<?php include __DIR__ . '/chatbot.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/cshub/assets/js/script.js?v=<?= $jsScriptVer ?>"></script>
<script src="/cshub/assets/js/theme.js?v=<?= $jsThemeVer ?>"></script>
</body>
</html>

