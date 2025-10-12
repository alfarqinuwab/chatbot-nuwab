<?php
/**
 * About Plugin Page Template
 * 
 * Displays AIMS compliance information and plugin details
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$plugin_data = get_plugin_data(WP_GPT_RAG_CHAT_PLUGIN_DIR . 'wp-gpt-rag-chat.php');
?>

<div class="wrap">
    <h1><?php esc_html_e('About Nuwab AI Assistant Plugin', 'wp-gpt-rag-chat'); ?></h1>
    
    <!-- Full White Card Container -->
    <div class="full-white-card">
        <div class="card-content">
            
            <!-- Plugin Information Section -->
            <div class="info-section">
                <h2 class="section-title"><?php esc_html_e('Plugin Information', 'wp-gpt-rag-chat'); ?></h2>
                <div class="info-grid">
                    <div class="info-item">
                        <label><?php esc_html_e('Plugin Name', 'wp-gpt-rag-chat'); ?></label>
                        <span class="value"><?php echo esc_html($plugin_data['Name'] ?? 'Nuwab AI Assistant'); ?></span>
                    </div>
                    <div class="info-item">
                        <label><?php esc_html_e('Version', 'wp-gpt-rag-chat'); ?></label>
                        <span class="value version"><?php echo esc_html($plugin_data['Version'] ?? WP_GPT_RAG_CHAT_VERSION); ?></span>
                    </div>
                    <div class="info-item">
                        <label><?php esc_html_e('Author', 'wp-gpt-rag-chat'); ?></label>
                        <span class="value"><?php echo esc_html($plugin_data['Author'] ?? 'Council of Representatives - IT Department'); ?></span>
                    </div>
                    <div class="info-item">
                        <label><?php esc_html_e('Last Updated', 'wp-gpt-rag-chat'); ?></label>
                        <span class="value"><?php echo esc_html(date('Y-m-d', filemtime(WP_GPT_RAG_CHAT_PLUGIN_DIR . 'wp-gpt-rag-chat.php'))); ?></span>
                    </div>
                </div>
                <div class="description-box">
                    <p><?php echo esc_html($plugin_data['Description'] ?? 'AI-powered chat assistant with RAG capabilities for the Council of Representatives'); ?></p>
                </div>
            </div>

            <!-- AIMS Compliance Information -->
            <div class="aims-section">
                <h2 class="section-title"><?php esc_html_e('AIMS Compliance Information', 'wp-gpt-rag-chat'); ?></h2>
                <p class="section-subtitle"><?php esc_html_e('Artificial Intelligence Management System (AIMS) Policy & Framework Compliance', 'wp-gpt-rag-chat'); ?></p>
                
                <div class="aims-content">
                    <div class="policy-details">
                        <h3><?php esc_html_e('Policy Details', 'wp-gpt-rag-chat'); ?></h3>
                        <div class="details-grid">
                            <div class="detail-item">
                                <label><?php esc_html_e('Policy Title', 'wp-gpt-rag-chat'); ?></label>
                                <span class="detail-value"><?php esc_html_e('Artificial Intelligence Management System (AIMS) Policy & Framework', 'wp-gpt-rag-chat'); ?></span>
                            </div>
                            <div class="detail-item">
                                <label><?php esc_html_e('Document ID', 'wp-gpt-rag-chat'); ?></label>
                                <span class="detail-value code">NWB-AIMS-01</span>
                            </div>
                            <div class="detail-item">
                                <label><?php esc_html_e('Version', 'wp-gpt-rag-chat'); ?></label>
                                <span class="detail-value badge">1.2</span>
                            </div>
                            <div class="detail-item">
                                <label><?php esc_html_e('Approval Date', 'wp-gpt-rag-chat'); ?></label>
                                <span class="detail-value">06-Oct-2025</span>
                            </div>
                            <div class="detail-item">
                                <label><?php esc_html_e('Approved By', 'wp-gpt-rag-chat'); ?></label>
                                <div class="detail-value">
                                    <ul class="approval-list">
                                        <li><?php esc_html_e('Quality Control & Assurance', 'wp-gpt-rag-chat'); ?></li>
                                        <li><?php esc_html_e('AIMS Manager', 'wp-gpt-rag-chat'); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="scope-section">
                        <h3><?php esc_html_e('Scope', 'wp-gpt-rag-chat'); ?></h3>
                        <div class="text-content">
                            <p><?php esc_html_e('The Artificial Intelligence Management System (AIMS) covers the governance, risk management, and oversight of artificial intelligence systems adopted by the Council of Representatives.', 'wp-gpt-rag-chat'); ?></p>
                            <p><?php esc_html_e('It applies to all off-the-shelf AI services (such as Microsoft Copilot and ChatGPT) used internally to enhance staff productivity, decision support, and information access.', 'wp-gpt-rag-chat'); ?></p>
                        </div>
                    </div>

                    <div class="purpose-section">
                        <h3><?php esc_html_e('Purpose', 'wp-gpt-rag-chat'); ?></h3>
                        <div class="text-content">
                            <p><?php esc_html_e('To ensure the responsible, transparent, and ethical use of AI systems within the Council of Representatives, supporting compliance with ISO 42001:2023 and Bahrain\'s Personal Data Protection Law (PDPL).', 'wp-gpt-rag-chat'); ?></p>
                        </div>
                    </div>

                    <div class="standards-section">
                        <h3><?php esc_html_e('Applicable Standards & References', 'wp-gpt-rag-chat'); ?></h3>
                        <div class="standards-list">
                            <div class="standard-item">
                                <strong>ISO 42001:2023</strong> – <?php esc_html_e('Artificial Intelligence Management Systems', 'wp-gpt-rag-chat'); ?>
                            </div>
                            <div class="standard-item">
                                <strong>Bahrain Personal Data Protection Law (PDPL – Law No. 30 of 2018)</strong>
                            </div>
                            <div class="standard-item">
                                <strong>CoR Information Security Management System (ISO 27001 aligned)</strong>
                            </div>
                        </div>
                    </div>

                    <div class="roles-section">
                        <h3><?php esc_html_e('Responsible Roles', 'wp-gpt-rag-chat'); ?></h3>
                        <div class="roles-list">
                            <div class="role-item"><?php esc_html_e('AIMS Manager', 'wp-gpt-rag-chat'); ?></div>
                            <div class="role-item"><?php esc_html_e('AIMS Committee', 'wp-gpt-rag-chat'); ?></div>
                            <div class="role-item"><?php esc_html_e('Information Security Manager', 'wp-gpt-rag-chat'); ?></div>
                            <div class="role-item"><?php esc_html_e('Top Management (Chairman & Secretary-General)', 'wp-gpt-rag-chat'); ?></div>
                        </div>
                    </div>

                    <div class="audit-section">
                        <h3><?php esc_html_e('Audit Information', 'wp-gpt-rag-chat'); ?></h3>
                        <div class="audit-grid">
                            <div class="audit-item">
                                <label><?php esc_html_e('Last Internal Audit Date', 'wp-gpt-rag-chat'); ?></label>
                                <span class="audit-value">08-Oct-2025</span>
                            </div>
                            <div class="audit-item">
                                <label><?php esc_html_e('Next Management Review Date', 'wp-gpt-rag-chat'); ?></label>
                                <span class="audit-value">01-Apr-2026</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- System Requirements -->
            <div class="requirements-section">
                <h2 class="section-title"><?php esc_html_e('System Requirements', 'wp-gpt-rag-chat'); ?></h2>
                <div class="requirements-grid">
                    <div class="requirement-item">
                        <label><?php esc_html_e('WordPress Version', 'wp-gpt-rag-chat'); ?></label>
                        <span class="req-value"><?php esc_html_e('5.0 or higher', 'wp-gpt-rag-chat'); ?></span>
                    </div>
                    <div class="requirement-item">
                        <label><?php esc_html_e('PHP Version', 'wp-gpt-rag-chat'); ?></label>
                        <span class="req-value"><?php esc_html_e('7.4 or higher', 'wp-gpt-rag-chat'); ?></span>
                    </div>
                    <div class="requirement-item">
                        <label><?php esc_html_e('Required Extensions', 'wp-gpt-rag-chat'); ?></label>
                        <div class="req-value">
                            <span class="extension-tag">cURL</span>
                            <span class="extension-tag">JSON</span>
                            <span class="extension-tag">OpenSSL</span>
                        </div>
                    </div>
                    <div class="requirement-item">
                        <label><?php esc_html_e('External Services', 'wp-gpt-rag-chat'); ?></label>
                        <div class="req-value">
                            <span class="service-tag">OpenAI API</span>
                            <span class="service-tag">Pinecone Vector Database</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
/* Full White Card Layout */
.full-white-card {
    background: #ffffff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin: 20px 0;
    overflow: hidden;
}

.card-content {
    padding: 40px;
}

/* Section Titles */
.section-title {
    color: #1d2327;
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #d1a85f;
}

.section-subtitle {
    color: #646970;
    font-size: 16px;
    margin-bottom: 30px;
    font-style: italic;
}

/* Plugin Information */
.info-section {
    margin-bottom: 40px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.info-item label {
    font-weight: 600;
    color: #1d2327;
    font-size: 14px;
}

.info-item .value {
    color: #646970;
    font-size: 16px;
}

.info-item .value.version {
    background: #d1a85f;
    color: white;
    padding: 4px 12px;
    border-radius: 4px;
    font-weight: bold;
    display: inline-block;
    width: fit-content;
}

.description-box {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #d1a85f;
    margin-top: 20px;
}

.description-box p {
    margin: 0;
    color: #1d2327;
    line-height: 1.6;
    font-size: 16px;
}

/* AIMS Compliance */
.aims-section {
    margin-bottom: 40px;
}

.aims-content {
    background: #f8f9fa;
    padding: 30px;
    border-radius: 8px;
    border: 1px solid #e1e5e9;
}

.aims-content h3 {
    color: #1d2327;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 15px;
    margin-top: 25px;
}

.aims-content h3:first-child {
    margin-top: 0;
}

/* Policy Details */
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.detail-item label {
    font-weight: 600;
    color: #1d2327;
    font-size: 14px;
}

.detail-value {
    color: #646970;
    font-size: 16px;
}

.detail-value.code {
    background: #e1e5e9;
    padding: 4px 8px;
    border-radius: 4px;
    font-family: monospace;
    display: inline-block;
    width: fit-content;
}

.detail-value.badge {
    background: #d1a85f;
    color: white;
    padding: 4px 12px;
    border-radius: 4px;
    font-weight: bold;
    display: inline-block;
    width: fit-content;
}

.approval-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.approval-list li {
    padding: 4px 0;
    color: #646970;
}

/* Text Content */
.text-content {
    background: #ffffff;
    padding: 20px;
    border-radius: 6px;
    border: 1px solid #e1e5e9;
    margin-bottom: 20px;
}

.text-content p {
    margin-bottom: 15px;
    line-height: 1.6;
    color: #1d2327;
}

.text-content p:last-child {
    margin-bottom: 0;
}

/* Standards and Roles */
.standards-list, .roles-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.standard-item, .role-item {
    background: #ffffff;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #e1e5e9;
    color: #1d2327;
    line-height: 1.5;
}

.standard-item strong {
    color: #d1a85f;
}

/* Audit Information */
.audit-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.audit-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
    background: #ffffff;
    padding: 20px;
    border-radius: 6px;
    border: 1px solid #e1e5e9;
}

.audit-item label {
    font-weight: 600;
    color: #1d2327;
    font-size: 14px;
}

.audit-value {
    color: #646970;
    font-size: 16px;
    font-weight: 500;
}


/* Requirements Section */
.requirements-section {
    margin-bottom: 20px;
}

.requirements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.requirement-item {
    background: #ffffff;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e1e5e9;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.requirement-item label {
    font-weight: 600;
    color: #1d2327;
    font-size: 14px;
}

.req-value {
    color: #646970;
    font-size: 16px;
}

.extension-tag, .service-tag {
    display: inline-block;
    background: #d1a85f;
    color: white;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    margin-right: 8px;
    margin-bottom: 4px;
}

.service-tag {
    background: #0073aa;
}

/* Responsive Design */
@media (max-width: 768px) {
    .card-content {
        padding: 20px;
    }
    
    .section-title {
        font-size: 20px;
    }
    
    .info-grid, .details-grid, .audit-grid, .requirements-grid {
        grid-template-columns: 1fr;
    }
    
    .aims-content {
        padding: 20px;
    }
}

@media (max-width: 480px) {
    .card-content {
        padding: 15px;
    }
    
    .section-title {
        font-size: 18px;
    }
}
</style>
