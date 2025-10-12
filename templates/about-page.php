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

<div class="wrap cornuwab-about-wrap">
    <!-- Hero Section -->
    <div class="about-hero">
        <div class="hero-content">
            <div class="hero-icon">
                <i class="fas fa-robot"></i>
            </div>
            <div class="hero-text">
                <h1><?php esc_html_e('Nuwab AI Assistant', 'wp-gpt-rag-chat'); ?></h1>
                <p class="hero-subtitle"><?php esc_html_e('AI-powered chat assistant with RAG capabilities for the Council of Representatives', 'wp-gpt-rag-chat'); ?></p>
                <div class="version-badge">
                    <span class="version-label"><?php esc_html_e('Version', 'wp-gpt-rag-chat'); ?></span>
                    <span class="version-number"><?php echo esc_html($plugin_data['Version'] ?? WP_GPT_RAG_CHAT_VERSION); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="about-main-content">
        <div class="content-grid">
            <!-- Plugin Information Card -->
            <div class="info-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h2><?php esc_html_e('Plugin Information', 'wp-gpt-rag-chat'); ?></h2>
                </div>
                <div class="card-content">
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-label"><?php esc_html_e('Plugin Name', 'wp-gpt-rag-chat'); ?></span>
                            <span class="info-value"><?php echo esc_html($plugin_data['Name'] ?? 'Nuwab AI Assistant'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><?php esc_html_e('Author', 'wp-gpt-rag-chat'); ?></span>
                            <span class="info-value"><?php echo esc_html($plugin_data['Author'] ?? 'Council of Representatives - IT Department'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><?php esc_html_e('Last Updated', 'wp-gpt-rag-chat'); ?></span>
                            <span class="info-value"><?php echo esc_html(date('Y-m-d', filemtime(WP_GPT_RAG_CHAT_PLUGIN_DIR . 'wp-gpt-rag-chat.php'))); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Requirements Card -->
            <div class="requirements-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h2><?php esc_html_e('System Requirements', 'wp-gpt-rag-chat'); ?></h2>
                </div>
                <div class="card-content">
                    <div class="requirements-list">
                        <div class="requirement-item">
                            <div class="req-icon">
                                <i class="fab fa-wordpress"></i>
                            </div>
                            <div class="req-details">
                                <span class="req-label"><?php esc_html_e('WordPress Version', 'wp-gpt-rag-chat'); ?></span>
                                <span class="req-value"><?php esc_html_e('5.0 or higher', 'wp-gpt-rag-chat'); ?></span>
                            </div>
                        </div>
                        <div class="requirement-item">
                            <div class="req-icon">
                                <i class="fab fa-php"></i>
                            </div>
                            <div class="req-details">
                                <span class="req-label"><?php esc_html_e('PHP Version', 'wp-gpt-rag-chat'); ?></span>
                                <span class="req-value"><?php esc_html_e('7.4 or higher', 'wp-gpt-rag-chat'); ?></span>
                            </div>
                        </div>
                        <div class="requirement-item">
                            <div class="req-icon">
                                <i class="fas fa-puzzle-piece"></i>
                            </div>
                            <div class="req-details">
                                <span class="req-label"><?php esc_html_e('Required Extensions', 'wp-gpt-rag-chat'); ?></span>
                                <div class="req-tags">
                                    <span class="req-tag">cURL</span>
                                    <span class="req-tag">JSON</span>
                                    <span class="req-tag">OpenSSL</span>
                                </div>
                            </div>
                        </div>
                        <div class="requirement-item">
                            <div class="req-icon">
                                <i class="fas fa-cloud"></i>
                            </div>
                            <div class="req-details">
                                <span class="req-label"><?php esc_html_e('External Services', 'wp-gpt-rag-chat'); ?></span>
                                <div class="req-tags">
                                    <span class="req-tag service">OpenAI API</span>
                                    <span class="req-tag service">Pinecone Vector DB</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AIMS Compliance Section -->
        <div class="aims-compliance-section">
            <div class="aims-header">
                <div class="aims-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="aims-title">
                    <h2><?php esc_html_e('AIMS Compliance Information', 'wp-gpt-rag-chat'); ?></h2>
                    <p><?php esc_html_e('Artificial Intelligence Management System (AIMS) Policy & Framework Compliance', 'wp-gpt-rag-chat'); ?></p>
                </div>
            </div>

            <div class="aims-content-grid">
                <!-- Policy Details Card -->
                <div class="aims-card policy-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3><?php esc_html_e('Policy Details', 'wp-gpt-rag-chat'); ?></h3>
                    </div>
                    <div class="card-content">
                        <div class="policy-info">
                            <div class="policy-item">
                                <span class="policy-label"><?php esc_html_e('Document ID', 'wp-gpt-rag-chat'); ?></span>
                                <span class="policy-value code">NWB-AIMS-01</span>
                            </div>
                            <div class="policy-item">
                                <span class="policy-label"><?php esc_html_e('Version', 'wp-gpt-rag-chat'); ?></span>
                                <span class="policy-value badge">1.2</span>
                            </div>
                            <div class="policy-item">
                                <span class="policy-label"><?php esc_html_e('Approval Date', 'wp-gpt-rag-chat'); ?></span>
                                <span class="policy-value">06-Oct-2025</span>
                            </div>
                            <div class="policy-item">
                                <span class="policy-label"><?php esc_html_e('Approved By', 'wp-gpt-rag-chat'); ?></span>
                                <div class="policy-value">
                                    <div class="approval-list">
                                        <span class="approval-item"><?php esc_html_e('Quality Control & Assurance', 'wp-gpt-rag-chat'); ?></span>
                                        <span class="approval-item"><?php esc_html_e('AIMS Manager', 'wp-gpt-rag-chat'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scope & Purpose Card -->
                <div class="aims-card scope-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-target"></i>
                        </div>
                        <h3><?php esc_html_e('Scope & Purpose', 'wp-gpt-rag-chat'); ?></h3>
                    </div>
                    <div class="card-content">
                        <div class="scope-content">
                            <div class="scope-item">
                                <h4><?php esc_html_e('Scope', 'wp-gpt-rag-chat'); ?></h4>
                                <p><?php esc_html_e('The Artificial Intelligence Management System (AIMS) covers the governance, risk management, and oversight of artificial intelligence systems adopted by the Council of Representatives.', 'wp-gpt-rag-chat'); ?></p>
                                <p><?php esc_html_e('It applies to all off-the-shelf AI services (such as Microsoft Copilot and ChatGPT) used internally to enhance staff productivity, decision support, and information access.', 'wp-gpt-rag-chat'); ?></p>
                            </div>
                            <div class="scope-item">
                                <h4><?php esc_html_e('Purpose', 'wp-gpt-rag-chat'); ?></h4>
                                <p><?php esc_html_e('To ensure the responsible, transparent, and ethical use of AI systems within the Council of Representatives, supporting compliance with ISO 42001:2023 and Bahrain\'s Personal Data Protection Law (PDPL).', 'wp-gpt-rag-chat'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Standards & References Card -->
                <div class="aims-card standards-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h3><?php esc_html_e('Standards & References', 'wp-gpt-rag-chat'); ?></h3>
                    </div>
                    <div class="card-content">
                        <div class="standards-list">
                            <div class="standard-item">
                                <div class="standard-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="standard-content">
                                    <strong>ISO 42001:2023</strong>
                                    <span><?php esc_html_e('Artificial Intelligence Management Systems', 'wp-gpt-rag-chat'); ?></span>
                                </div>
                            </div>
                            <div class="standard-item">
                                <div class="standard-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="standard-content">
                                    <strong>Bahrain PDPL (Law No. 30 of 2018)</strong>
                                    <span><?php esc_html_e('Personal Data Protection Law', 'wp-gpt-rag-chat'); ?></span>
                                </div>
                            </div>
                            <div class="standard-item">
                                <div class="standard-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="standard-content">
                                    <strong>CoR ISMS (ISO 27001 aligned)</strong>
                                    <span><?php esc_html_e('Information Security Management System', 'wp-gpt-rag-chat'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Responsible Roles Card -->
                <div class="aims-card roles-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3><?php esc_html_e('Responsible Roles', 'wp-gpt-rag-chat'); ?></h3>
                    </div>
                    <div class="card-content">
                        <div class="roles-list">
                            <div class="role-item">
                                <div class="role-icon">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <span><?php esc_html_e('AIMS Manager', 'wp-gpt-rag-chat'); ?></span>
                            </div>
                            <div class="role-item">
                                <div class="role-icon">
                                    <i class="fas fa-users-cog"></i>
                                </div>
                                <span><?php esc_html_e('AIMS Committee', 'wp-gpt-rag-chat'); ?></span>
                            </div>
                            <div class="role-item">
                                <div class="role-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <span><?php esc_html_e('Information Security Manager', 'wp-gpt-rag-chat'); ?></span>
                            </div>
                            <div class="role-item">
                                <div class="role-icon">
                                    <i class="fas fa-crown"></i>
                                </div>
                                <span><?php esc_html_e('Top Management (Chairman & Secretary-General)', 'wp-gpt-rag-chat'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Audit Information Card -->
                <div class="aims-card audit-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <h3><?php esc_html_e('Audit Information', 'wp-gpt-rag-chat'); ?></h3>
                    </div>
                    <div class="card-content">
                        <div class="audit-info">
                            <div class="audit-item">
                                <div class="audit-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="audit-details">
                                    <span class="audit-label"><?php esc_html_e('Last Internal Audit', 'wp-gpt-rag-chat'); ?></span>
                                    <span class="audit-value">08-Oct-2025</span>
                                </div>
                            </div>
                            <div class="audit-item">
                                <div class="audit-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="audit-details">
                                    <span class="audit-label"><?php esc_html_e('Next Management Review', 'wp-gpt-rag-chat'); ?></span>
                                    <span class="audit-value">01-Apr-2026</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern About Page Styles */
.cornuwab-about-wrap {
    margin-top: 20px;
}

/* Hero Section */
.about-hero {
    background: #ffffff;
    color: #1d2327;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e1e5e9;
}

.hero-content {
    display: flex;
    align-items: center;
    gap: 20px;
}

.hero-icon {
    width: 50px;
    height: 50px;
    background: #0073aa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.hero-text h1 {
    margin: 0 0 5px 0;
    font-size: 24px;
    font-weight: 600;
    color: #1d2327;
}

.hero-subtitle {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #646970;
    line-height: 1.4;
}

.version-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f8f9fa;
    padding: 4px 12px;
    border-radius: 4px;
    border: 1px solid #e1e5e9;
}

.version-label {
    font-size: 12px;
    color: #646970;
}

.version-number {
    font-weight: 600;
    font-size: 14px;
    color: #1d2327;
}

/* Main Content */
.about-main-content {
    margin-bottom: 30px;
}

.content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 40px;
}

/* Card Styles */
.info-card, .requirements-card {
    background: #fff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.card-header {
    background: #f8f9fa;
    padding: 20px;
    border-bottom: 1px solid #e1e5e9;
    display: flex;
    align-items: center;
    gap: 15px;
}

.card-icon {
    width: 40px;
    height: 40px;
    background: #0073aa;
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.card-header h2 {
    margin: 0;
    color: #1d2327;
    font-size: 18px;
    font-weight: 600;
}

.card-content {
    padding: 20px;
}

/* Info List */
.info-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 500;
    color: #646970;
    font-size: 14px;
}

.info-value {
    color: #1d2327;
    font-weight: 500;
    font-size: 14px;
}

/* Requirements List */
.requirements-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.requirement-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e1e5e9;
}

.req-icon {
    width: 40px;
    height: 40px;
    background: #0073aa;
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.req-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.req-label {
    font-weight: 500;
    color: #1d2327;
    font-size: 14px;
}

.req-value {
    color: #646970;
    font-size: 14px;
}

.req-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 5px;
}

.req-tag {
    background: #d1a85f;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.req-tag.service {
    background: #0073aa;
}

/* AIMS Compliance Section */
.aims-compliance-section {
    margin-top: 40px;
}

.aims-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e1e5e9;
}

.aims-icon {
    width: 60px;
    height: 60px;
    background: #d1a85f;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.aims-title h2 {
    margin: 0 0 5px 0;
    color: #1d2327;
    font-size: 24px;
    font-weight: 600;
}

.aims-title p {
    margin: 0;
    color: #646970;
    font-size: 16px;
}

.aims-content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.aims-card {
    background: #fff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.aims-card .card-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #e1e5e9;
    display: flex;
    align-items: center;
    gap: 12px;
}

.aims-card .card-icon {
    width: 32px;
    height: 32px;
    background: #d1a85f;
    color: white;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.aims-card h3 {
    margin: 0;
    color: #1d2327;
    font-size: 16px;
    font-weight: 600;
}

.aims-card .card-content {
    padding: 20px;
}

/* Policy Info */
.policy-info {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.policy-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.policy-item:last-child {
    border-bottom: none;
}

.policy-label {
    font-weight: 500;
    color: #646970;
    font-size: 14px;
}

.policy-value {
    color: #1d2327;
    font-weight: 500;
    font-size: 14px;
}

.policy-value.code {
    background: #e1e5e9;
    padding: 4px 8px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 12px;
}

.policy-value.badge {
    background: #d1a85f;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.approval-list {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.approval-item {
    color: #646970;
    font-size: 13px;
}

/* Scope Content */
.scope-content {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.scope-item h4 {
    margin: 0 0 10px 0;
    color: #1d2327;
    font-size: 16px;
    font-weight: 600;
}

.scope-item p {
    margin: 0 0 10px 0;
    color: #646970;
    line-height: 1.6;
    font-size: 14px;
}

.scope-item p:last-child {
    margin-bottom: 0;
}

/* Standards List */
.standards-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.standard-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e1e5e9;
}

.standard-icon {
    width: 24px;
    height: 24px;
    color: #00a32a;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.standard-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.standard-content strong {
    color: #1d2327;
    font-size: 14px;
    font-weight: 600;
}

.standard-content span {
    color: #646970;
    font-size: 13px;
}

/* Roles List */
.roles-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.role-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e1e5e9;
}

.role-icon {
    width: 24px;
    height: 24px;
    color: #0073aa;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.role-item span {
    color: #1d2327;
    font-size: 14px;
    font-weight: 500;
}

/* Audit Info */
.audit-info {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.audit-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e1e5e9;
}

.audit-icon {
    width: 24px;
    height: 24px;
    color: #d1a85f;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.audit-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.audit-label {
    color: #646970;
    font-size: 13px;
    font-weight: 500;
}

.audit-value {
    color: #1d2327;
    font-size: 14px;
    font-weight: 600;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-content {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .content-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .aims-content-grid {
        grid-template-columns: 1fr;
    }
    
    .aims-header {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .about-hero {
        padding: 15px;
    }
    
    .hero-text h1 {
        font-size: 20px;
    }
    
    .hero-subtitle {
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .about-hero {
        padding: 15px;
    }
    
    .hero-text h1 {
        font-size: 18px;
    }
    
    .hero-subtitle {
        font-size: 12px;
    }
    
    .card-content {
        padding: 15px;
    }
    
    .aims-card .card-content {
        padding: 15px;
    }
}
</style>
