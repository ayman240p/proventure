<?php
/**
 * includes/report_modal.php
 * Universal Modal for reporting live micro-services.
 * Compatible with any page having buttons with data-service-id and data-service-title.
 */
$currentUserLoggedIn = isLoggedIn();
?>

<!-- Universal Report Listing Modal -->
<div class="modal fade" id="reportServiceModal" tabindex="-1" aria-labelledby="reportServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-danger bg-opacity-10 border-0 pt-4 px-4 pb-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-flag-fill fs-6"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-danger mb-0" id="reportServiceModalLabel">
                            <?= __('Report Listing') ?>
                        </h5>
                        <p class="text-muted small mb-0"><?= __('Help keep the ProVenture community safe and authentic') ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="/cshub/report_service.php" method="POST" id="reportListingForm">
                <div class="modal-body p-4">
                    <input type="hidden" name="service_id" id="reportModalServiceId" value="">
                    <input type="hidden" name="redirect_url" id="reportModalRedirectUrl" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/cshub/index.php'); ?>">

                    <!-- Service Being Reported Preview -->
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.72rem;"><?= __('Reporting Service:') ?></small>
                        <span class="fw-bold text-dark fs-6 d-block text-truncate" id="reportModalServiceTitle">-</span>
                    </div>

                    <!-- Reason Radio List -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase"><?= __('Reason for reporting') ?> <span class="text-danger">*</span></label>
                        <div class="d-flex flex-column gap-2">
                            <label class="form-check form-check-custom p-2 rounded-3 border bg-body-hover cursor-pointer mb-0">
                                <input class="form-check-input ms-1 me-2" type="radio" name="reason" value="spam" id="reasonSpam" checked>
                                <span class="form-check-label">
                                    <strong class="d-block text-dark"><?= __('Spam or Scam') ?></strong>
                                    <small class="text-muted"><?= __('Phishing, fake reviews, repeated duplicates, or unsolicited ads') ?></small>
                                </span>
                            </label>

                            <label class="form-check form-check-custom p-2 rounded-3 border bg-body-hover cursor-pointer mb-0">
                                <input class="form-check-input ms-1 me-2" type="radio" name="reason" value="malicious" id="reasonMalicious">
                                <span class="form-check-label">
                                    <strong class="d-block text-danger"><?= __('Malicious or Fraudulent Content') ?></strong>
                                    <small class="text-muted"><?= __('Malware links, credential theft, payment fraud, or impersonation') ?></small>
                                </span>
                            </label>

                            <label class="form-check form-check-custom p-2 rounded-3 border bg-body-hover cursor-pointer mb-0">
                                <input class="form-check-input ms-1 me-2" type="radio" name="reason" value="misleading" id="reasonMisleading">
                                <span class="form-check-label">
                                    <strong class="d-block text-dark"><?= __('Misleading or Inaccurate Information') ?></strong>
                                    <small class="text-muted"><?= __('False claims, hidden pricing, or plagiarized portfolio work') ?></small>
                                </span>
                            </label>

                            <label class="form-check form-check-custom p-2 rounded-3 border bg-body-hover cursor-pointer mb-0">
                                <input class="form-check-input ms-1 me-2" type="radio" name="reason" value="inappropriate" id="reasonInappropriate">
                                <span class="form-check-label">
                                    <strong class="d-block text-dark"><?= __('Inappropriate or Harmful') ?></strong>
                                    <small class="text-muted"><?= __('Harassment, offensive text, illegal activities, or adult material') ?></small>
                                </span>
                            </label>

                            <label class="form-check form-check-custom p-2 rounded-3 border bg-body-hover cursor-pointer mb-0">
                                <input class="form-check-input ms-1 me-2" type="radio" name="reason" value="other" id="reasonOther">
                                <span class="form-check-label">
                                    <strong class="d-block text-dark"><?= __('Other Issue') ?></strong>
                                    <small class="text-muted"><?= __('Any other violation of our community standards') ?></small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Details Textarea -->
                    <div class="mb-3">
                        <label for="reportDetails" class="form-label fw-bold small text-muted text-uppercase">
                            <?= __('Additional Details (Optional)') ?>
                        </label>
                        <textarea class="form-control" id="reportDetails" name="details" rows="3" 
                                  placeholder="<?= __('Provide extra context to help our administrators investigate...') ?>" maxlength="500"></textarea>
                    </div>

                    <?php if (!$currentUserLoggedIn): ?>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small text-muted mb-1"><?= __('Your Name') ?></label>
                                <input type="text" name="reporter_name" class="form-control form-control-sm" placeholder="<?= __('Optional') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted mb-1"><?= __('Your Email') ?></label>
                                <input type="email" name="reporter_email" class="form-control form-control-sm" placeholder="<?= __('Optional') ?>">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="modal-footer border-0 bg-light px-4 py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <?= __('Cancel') ?>
                    </button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm" id="submitReportBtn">
                        <i class="bi bi-send-fill me-1"></i> <?= __('Submit Report') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reportModal = document.getElementById('reportServiceModal');
    if (!reportModal) return;

    reportModal.addEventListener('show.bs.modal', function(event) {
        const triggerBtn = event.relatedTarget;
        if (!triggerBtn) return;

        const serviceId = triggerBtn.getAttribute('data-service-id') || '';
        const serviceTitle = triggerBtn.getAttribute('data-service-title') || 'Selected Micro-Service';
        const redirectUrl = triggerBtn.getAttribute('data-redirect-url') || window.location.pathname + window.location.search;

        const idInput = document.getElementById('reportModalServiceId');
        const titleSpan = document.getElementById('reportModalServiceTitle');
        const redirectInput = document.getElementById('reportModalRedirectUrl');

        if (idInput) idInput.value = serviceId;
        if (titleSpan) titleSpan.textContent = serviceTitle;
        if (redirectInput && redirectUrl) redirectInput.value = redirectUrl;
    });
});
</script>
