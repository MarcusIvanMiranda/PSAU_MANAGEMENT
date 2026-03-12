-- Insert 50 sample records for header_id 1
-- NAP Records Inventory and Appraisal Form - Fields 9-20

INSERT INTO nap_records (
    header_id, 
    records_series_title, 
    records_description, 
    period_covered_from, 
    volume, 
    records_medium, 
    restrictions, 
    location_of_records, 
    request_frequency, 
    duplication_value, 
    time_value, 
    utility_value, 
    retention_period_active, 
    retention_period_storage, 
    retention_period_total, 
    disposition_provision
) VALUES 

-- Personnel Records (1-10)
(1, 'Personnel Files - Teaching Staff', 'Individual personnel folders for faculty members', 'Jan-Dec 2025', '15 boxes', 'Paper', 'Confidential', 'HR Office Cabinet 1', 'Monthly', 'N/A', 'T', 'Adm', 3, 7, 10, 'Transfer to NAP'),
(1, 'Personnel Files - Admin Staff', 'Individual personnel folders for administrative staff', 'Jan-Dec 2025', '12 boxes', 'Paper', 'Confidential', 'HR Office Cabinet 2', 'Monthly', 'N/A', 'T', 'Adm', 3, 7, 10, 'Transfer to NAP'),
(1, 'Service Records', 'Employment history and service records', '2020-2025', '8 folders', 'Paper', 'Confidential', 'HR Vault', 'Quarterly', 'N/A', 'P', 'L', 0, 0, 0, 'Permanent'),
(1, 'Performance Evaluations', 'Annual performance appraisal documents', '2020-2025', '6 boxes', 'Paper', 'Confidential', 'HR Office Shelf A', 'Annually', 'N/A', 'T', 'Adm', 5, 5, 10, 'Destroy'),
(1, 'Leave Applications', 'Processed leave forms and approvals', '2024-2025', '4 boxes', 'Paper', 'Restricted', 'HR Filing Room', 'Daily', 'N/A', 'T', 'Adm', 1, 2, 3, 'Destroy'),
(1, 'Overtime Records', 'Overtime authorization and payment records', '2024-2025', '3 boxes', 'Paper', 'Restricted', 'HR Filing Room', 'Monthly', 'N/A', 'T', 'F', 3, 5, 8, 'Transfer to NAP'),
(1, 'Job Applications', 'Received job applications and CVs', '2023-2025', '5 boxes', 'Paper', 'Confidential', 'HR Storage Room', 'Quarterly', 'N/A', 'T', 'Adm', 2, 3, 5, 'Destroy'),
(1, 'Training Certificates', 'Staff training and seminar certificates', '2020-2025', '2 boxes', 'Paper', 'Open Access', 'HR Office Shelf B', 'Annually', 'N/A', 'T', 'Adm', 5, 5, 10, 'Transfer to NAP'),
(1, 'Disciplinary Records', 'Administrative cases and disciplinary actions', '2020-2025', '1 box', 'Paper', 'Top Secret', 'HR Vault', 'ANA', 'N/A', 'P', 'L', 0, 0, 0, 'Permanent'),
(1, 'Retirement Documents', 'Retirement application and processing papers', '2020-2025', '3 folders', 'Paper', 'Confidential', 'HR Vault', 'Annually', 'N/A', 'P', 'L', 0, 0, 0, 'Permanent'),

-- Financial Records (11-20)
(1, 'Annual Budget Proposals', 'Budget preparation documents and justifications', '2020-2025', '10 folders', 'Paper', 'Restricted', 'Budget Office Cabinet', 'Annually', 'N/A', 'P', 'F', 0, 0, 0, 'Permanent'),
(1, 'Budget Execution Reports', 'Monthly and quarterly budget utilization', '2024-2025', '8 folders', 'Paper', 'Restricted', 'Budget Office Shelf', 'Quarterly', 'N/A', 'T', 'F', 5, 5, 10, 'Transfer to NAP'),
(1, 'Vouchers and Receipts', 'Payment vouchers with supporting documents', '2024-2025', '25 boxes', 'Paper', 'Restricted', 'Accounting Storage', 'Daily', 'N/A', 'T', 'F', 5, 5, 10, 'Transfer to NAP'),
(1, 'Cash Advance Records', 'Liquidation and cash advance documents', '2024-2025', '12 boxes', 'Paper', 'Restricted', 'Accounting Vault', 'Weekly', 'N/A', 'T', 'F', 3, 5, 8, 'Destroy'),
(1, 'Bank Statements', 'Monthly bank reconciliation statements', '2024-2025', '6 folders', 'Electronic', 'Confidential', 'Cloud Storage', 'Monthly', 'N/A', 'T', 'F', 5, 5, 10, 'Digital Archive'),
(1, 'General Ledger', 'Annual general ledger entries', '2020-2025', '10 binders', 'Paper', 'Restricted', 'Accounting Archive', 'Annually', 'N/A', 'P', 'F', 0, 0, 0, 'Permanent'),
(1, 'Payroll Records', 'Monthly payroll registers and summaries', '2024-2025', '15 boxes', 'Electronic', 'Confidential', 'Payroll System', 'Monthly', 'N/A', 'T', 'F', 5, 5, 10, 'Digital Archive'),
(1, 'Contracts and MOAs', 'Signed agreements with suppliers and partners', '2020-2025', '8 folders', 'Paper', 'Restricted', 'Legal Office Cabinet', 'Quarterly', 'N/A', 'P', 'L', 0, 0, 0, 'Permanent'),
(1, 'Purchase Orders', 'Procurement and purchase order documents', '2024-2025', '20 boxes', 'Paper', 'Restricted', 'Supply Office', 'Daily', 'N/A', 'T', 'F', 3, 5, 8, 'Destroy'),
(1, 'Bidding Documents', 'Procurement bidding and canvass records', '2023-2025', '15 folders', 'Paper', 'Restricted', 'BAC Office', 'Quarterly', 'N/A', 'T', 'F', 5, 10, 15, 'Transfer to NAP'),

-- Academic Records (21-30)
(1, 'Student Academic Records', 'Transcripts and academic histories', '2020-2025', '50 boxes', 'Paper', 'Confidential', 'Registrar Vault', 'Daily', 'N/A', 'P', 'L', 0, 0, 0, 'Permanent'),
(1, 'Enrollment Reports', 'Semester enrollment statistics', '2020-2025', '10 folders', 'Electronic', 'Open Access', 'Registrar Database', 'Semi-Annually', 'N/A', 'T', 'Adm', 5, 5, 10, 'Digital Archive'),
(1, 'Graduation Records', 'Graduation clearance and diploma issuance', '2020-2025', '15 folders', 'Paper', 'Confidential', 'Registrar Office', 'Annually', 'N/A', 'P', 'L', 0, 0, 0, 'Permanent'),
(1, 'Scholarship Records', 'Scholarship applications and monitoring', '2020-2025', '8 folders', 'Paper', 'Confidential', 'OSAS Cabinet', 'Semester', 'N/A', 'T', 'Adm', 5, 5, 10, 'Transfer to NAP'),
(1, 'Class Schedules', 'Semester course offerings and schedules', '2020-2025', '12 folders', 'Electronic', 'Open Access', 'Academic Portal', 'Semi-Annually', 'N/A', 'T', 'Adm', 2, 3, 5, 'Digital Archive'),
(1, 'Faculty Loading', 'Teaching assignments and faculty load', '2020-2025', '8 folders', 'Paper', 'Restricted', 'Acad Office Cabinet', 'Semi-Annually', 'N/A', 'T', 'Adm', 3, 5, 8, 'Destroy'),
(1, 'Curriculum Guides', 'Course syllabi and curriculum documents', '2020-2025', '6 boxes', 'Paper', 'Open Access', 'Acad Office Shelf', 'Annually', 'N/A', 'T', 'Adm', 5, 10, 15, 'Transfer to NAP'),
(1, 'Grade Sheets', 'Official class records and grade reports', '2020-2025', '30 boxes', 'Paper', 'Confidential', 'Registrar Vault', 'Semi-Annually', 'N/A', 'P', 'L', 0, 0, 0, 'Permanent'),
(1, 'OJT Records', 'Practicum and internship documentation', '2020-2025', '10 folders', 'Paper', 'Restricted', 'OJT Office', 'Annually', 'N/A', 'T', 'Adm', 5, 5, 10, 'Transfer to NAP'),
(1, 'Research Proposals', 'Faculty and student research applications', '2020-2025', '12 folders', 'Paper', 'Open Access', 'RDE Office', 'Quarterly', 'N/A', 'T', 'Arc', 10, 10, 20, 'Transfer to NAP'),

-- Administrative Records (31-40)
(1, 'Board Resolutions', 'Official board meeting resolutions', '2020-2025', '5 folders', 'Paper', 'Restricted', 'Board Secretary Office', 'Quarterly', 'N/A', 'P', 'L', 0, 0, 0, 'Permanent'),
(1, 'Minutes of Meetings', 'Various committee meeting minutes', '2020-2025', '15 folders', 'Paper', 'Restricted', 'Admin Office Archive', 'Monthly', 'N/A', 'T', 'Adm', 5, 10, 15, 'Transfer to NAP'),
(1, 'Memorandum Orders', 'University memoranda and circulars', '2020-2025', '8 folders', 'Electronic', 'Open Access', 'Admin Portal', 'Daily', 'N/A', 'T', 'Adm', 2, 3, 5, 'Digital Archive'),
(1, 'Official Communications', 'Letters to and from other agencies', '2020-2025', '12 boxes', 'Paper', 'Restricted', 'Records Room', 'Daily', 'N/A', 'T', 'Adm', 5, 5, 10, 'Transfer to NAP'),
(1, 'Annual Reports', 'University annual accomplishment reports', '2020-2025', '6 folders', 'Paper', 'Open Access', 'OP Cabinet', 'Annually', 'N/A', 'P', 'Arc', 0, 0, 0, 'Permanent'),
(1, 'Strategic Plans', 'University strategic planning documents', '2020-2025', '4 folders', 'Paper', 'Restricted', 'OP Cabinet', 'Annually', 'N/A', 'P', 'Arc', 0, 0, 0, 'Permanent'),
(1, 'Policy Manuals', 'University policies and guidelines', '2020-2025', '3 folders', 'Paper', 'Open Access', 'Admin Office', 'Quarterly', 'N/A', 'P', 'Adm', 0, 0, 0, 'Permanent'),
(1, 'Accreditation Documents', 'CHED and accrediting agency submissions', '2020-2025', '20 boxes', 'Paper', 'Restricted', 'QA Office', 'Annually', 'N/A', 'P', 'Arc', 0, 0, 0, 'Permanent'),
(1, 'ISO Records', 'ISO certification and audit documents', '2020-2025', '10 folders', 'Electronic', 'Restricted', 'QA Office', 'Quarterly', 'N/A', 'P', 'Adm', 0, 0, 0, 'Permanent'),
(1, 'Asset Inventories', 'Physical inventory and property records', '2020-2025', '8 folders', 'Paper', 'Restricted', 'Supply Office', 'Annually', 'N/A', 'T', 'F', 5, 5, 10, 'Transfer to NAP'),

-- Legal and Special Records (41-50)
(1, 'Land Titles and Deeds', 'Property ownership documents', '2020-2025', '2 folders', 'Paper', 'Top Secret', 'President Vault', 'ANA', 'N/A', 'P', 'L', 0, 0, 0, 'Permanent'),
(1, 'Building Permits', 'Construction and renovation permits', '2020-2025', '6 folders', 'Paper', 'Restricted', 'Physical Plant Office', 'Quarterly', 'N/A', 'T', 'L', 10, 10, 20, 'Transfer to NAP'),
(1, 'Legal Opinions', 'Legal counsel opinions and advice', '2020-2025', '4 folders', 'Paper', 'Confidential', 'Legal Office Vault', 'Quarterly', 'N/A', 'P', 'L', 0, 0, 0, 'Permanent'),
(1, 'Litigation Files', 'Pending and closed court cases', '2020-2025', '3 folders', 'Paper', 'Top Secret', 'Legal Office Vault', 'Monthly', 'N/A', 'P', 'L', 0, 0, 0, 'Permanent'),
(1, 'Insurance Policies', 'University insurance coverage documents', '2020-2025', '5 folders', 'Paper', 'Restricted', 'Finance Office', 'Annually', 'N/A', 'T', 'F', 5, 5, 10, 'Transfer to NAP'),
(1, 'Alumni Records', 'Alumni database and correspondences', '2020-2025', '8 folders', 'Electronic', 'Open Access', 'Alumni Portal', 'Quarterly', 'N/A', 'T', 'Adm', 5, 5, 10, 'Digital Archive'),
(1, 'Library Acquisitions', 'Book and material purchase records', '2020-2025', '10 folders', 'Paper', 'Open Access', 'Library Office', 'Annually', 'N/A', 'T', 'Adm', 5, 5, 10, 'Transfer to NAP'),
(1, 'IT Equipment Logs', 'Computer and device inventory', '2020-2025', '6 folders', 'Electronic', 'Restricted', 'IT Office', 'Quarterly', 'N/A', 'T', 'F', 3, 5, 8, 'Digital Archive'),
(1, 'Security Incident Reports', 'Campus security and incident logs', '2020-2025', '4 folders', 'Paper', 'Confidential', 'Security Office', 'Monthly', 'N/A', 'T', 'L', 5, 5, 10, 'Transfer to NAP'),
(1, 'Disaster Recovery Plans', 'Business continuity and emergency plans', '2020-2025', '3 folders', 'Paper', 'Restricted', 'Admin Office Safe', 'Annually', 'N/A', 'P', 'Adm', 0, 0, 0, 'Permanent');
