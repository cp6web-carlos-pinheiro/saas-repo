

You are acting as a Senior Solution Architect and Independent Technical Auditor.

Your role is to perform a COMPLETE AUDIT of the implementation generated from all previous prompts related to the Maestro FY26/27 deployment governance, responsibility matrix alignment, modules implementation, integrations, CR execution model, deployment workflows, support model, and operational processes.

Your objective is NOT to implement new features.
Your objective is to VALIDATE, CROSS-CHECK, IDENTIFY GAPS, and PRODUCE AN AUDIT REPORT confirming whether the implementation is fully compliant with the specifications, architecture, governance model, and operational requirements previously defined.

=====================================================
AUDIT OBJECTIVES
=====================================================

Perform a full audit covering:

1. Functional compliance
2. Architectural compliance
3. Governance/RACI compliance
4. Deployment model compliance
5. Security and access control
6. Integration compliance
7. Operational support compliance
8. Change Request governance
9. SLA / support readiness
10. Code quality and maintainability
11. Database consistency
12. API consistency
13. UI/UX consistency
14. Logging and observability
15. Error handling
16. Scalability and extensibility
17. Technical debt identification
18. Missing requirements detection
19. Incorrect assumptions
20. Hidden coupling or fragile implementations

=====================================================
AUDIT EXECUTION RULES
=====================================================

You MUST:

- Inspect ALL generated code
- Inspect ALL database migrations
- Inspect ALL APIs
- Inspect ALL services
- Inspect ALL queues/jobs/workers
- Inspect ALL UI flows
- Inspect ALL middleware/authentication
- Inspect ALL integrations
- Inspect ALL deployment logic
- Inspect ALL configuration files
- Inspect ALL environment variables
- Inspect ALL tests
- Inspect ALL CR-related workflows
- Inspect ALL support and incident flows
- Inspect ALL approval/governance logic

You MUST compare the implementation against:

- Original specifications
- Functional requirements
- Deployment governance model
- Responsibility matrix
- RACI definitions
- SOW operational boundaries
- Security requirements
- Architecture standards
- Laravel best practices
- Enterprise-grade operational standards

=====================================================
MANDATORY AUDIT VALIDATIONS
=====================================================

Validate ALL of the following:

-----------------------------------------------------
1. ARCHITECTURE VALIDATION
-----------------------------------------------------

Verify:

- Proper modular architecture
- Separation of concerns
- Domain isolation
- Service layer consistency
- Repository pattern consistency
- Dependency injection usage
- Queue/event architecture
- Scalability considerations
- Avoidance of God classes
- Avoidance of duplicated business logic
- Proper transaction boundaries

Identify:
- Tight coupling
- Circular dependencies
- Anti-patterns
- Hidden business rules
- Hardcoded logic
- Fragile implementations

-----------------------------------------------------
2. DATABASE VALIDATION
-----------------------------------------------------

Verify:

- Tables follow normalization standards
- Correct indexes exist
- Foreign keys are correct
- Cascade rules are safe
- Constraints are present
- Naming conventions are consistent
- Audit fields exist
- Versioning works correctly
- Soft delete strategy consistency
- Migration rollback safety

Detect:
- Missing indexes
- Potential deadlocks
- Performance risks
- Orphan data risks
- Missing constraints
- Data integrity violations

-----------------------------------------------------
3. API VALIDATION
-----------------------------------------------------

Verify:

- REST consistency
- Validation rules
- Error responses
- Authentication enforcement
- Authorization enforcement
- Idempotency where needed
- Pagination consistency
- Response standardization
- API versioning
- Rate limiting readiness

Detect:
- Missing validation
- Insecure endpoints
- Business rule bypasses
- Missing authorization checks
- Inconsistent contracts

-----------------------------------------------------
4. SECURITY VALIDATION
-----------------------------------------------------

Verify:

- RBAC correctness
- SSO integration boundaries
- Session handling
- CSRF protection
- XSS prevention
- SQL injection prevention
- File upload safety
- Secrets management
- Environment variable usage
- Audit logging

Detect:
- Privilege escalation paths
- Hardcoded credentials
- Unsafe queries
- Missing authorization
- Insecure storage

-----------------------------------------------------
5. DEPLOYMENT & DEVOPS VALIDATION
-----------------------------------------------------

Verify:

- Environment separation
- CI/CD readiness
- Rollback readiness
- Deployment reproducibility
- Configuration externalization
- Health checks
- Logging strategy
- Monitoring readiness
- Queue supervision
- Failure recovery

Detect:
- Environment-specific hardcoding
- Missing rollback support
- Unsafe deployment steps
- Missing operational observability

-----------------------------------------------------
6. RESPONSIBILITY MATRIX COMPLIANCE
-----------------------------------------------------

Verify that the implementation respects the intended operational model:

P&G Responsibilities:
- infrastructure
- VMs
- SSO
- integrations
- core deployment ownership

BG Responsibilities:
- operational support
- co-configuration
- change requests
- hypercare
- support readiness

Detect:
- functionality violating operational boundaries
- ownership conflicts
- deployment ownership inconsistencies
- incorrect assumptions about support responsibilities

-----------------------------------------------------
7. CHANGE REQUEST GOVERNANCE VALIDATION
-----------------------------------------------------

Verify:

- CR workflows exist
- Approval workflows exist
- Audit trail exists
- Status transitions are controlled
- Assignment rules are enforced
- SLA tracking exists
- Reporting exists
- Release linkage exists

Detect:
- uncontrolled changes
- missing approvals
- missing auditability
- bypass paths

-----------------------------------------------------
8. OBSERVABILITY & SUPPORT VALIDATION
-----------------------------------------------------

Verify:

- Structured logging
- Error tracking
- Incident traceability
- Correlation IDs
- Retry policies
- Queue failure handling
- Monitoring readiness
- Operational diagnostics

Detect:
- silent failures
- missing logs
- poor troubleshooting capability
- non-actionable errors

-----------------------------------------------------
9. UI/UX VALIDATION
-----------------------------------------------------

Verify:

- Consistent navigation
- Responsive behavior
- Accessibility basics
- Error feedback clarity
- Form validation UX
- Permission-based UI rendering
- Loading states
- Empty states

Detect:
- inconsistent workflows
- broken flows
- confusing UX
- missing validations

-----------------------------------------------------
10. CODE QUALITY VALIDATION
-----------------------------------------------------

Verify:

- PSR compliance
- Naming conventions
- Readability
- Documentation quality
- Testability
- Reusability
- Maintainability

Detect:
- dead code
- duplicated logic
- over-engineering
- under-engineering
- low cohesion
- excessive complexity

=====================================================
MANDATORY OUTPUT FORMAT
=====================================================

Generate a COMPLETE AUDIT REPORT with the following sections:

# EXECUTIVE SUMMARY

- Overall audit score
- Production readiness score
- Security score
- Maintainability score
- Scalability score
- Governance compliance score

# CRITICAL ISSUES

List ALL critical findings.

For each:
- Severity
- Component
- Description
- Risk
- Impact
- Recommended fix

# HIGH PRIORITY ISSUES

# MEDIUM PRIORITY ISSUES

# LOW PRIORITY ISSUES

# ARCHITECTURE REVIEW

# DATABASE REVIEW

# API REVIEW

# SECURITY REVIEW

# DEPLOYMENT/DEVOPS REVIEW

# RESPONSIBILITY MATRIX COMPLIANCE REVIEW

# CHANGE MANAGEMENT REVIEW

# OBSERVABILITY REVIEW

# UI/UX REVIEW

# CODE QUALITY REVIEW

# MISSING REQUIREMENTS

Explicitly identify:
- missing features
- partially implemented requirements
- incorrectly implemented requirements

# RISK ANALYSIS

Include:
- operational risks
- scalability risks
- maintainability risks
- governance risks
- security risks

# TECHNICAL DEBT

List:
- shortcuts
- fragile areas
- future risks
- refactoring recommendations

# FINAL VERDICT

Classify the solution as:

- APPROVED FOR PRODUCTION
- APPROVED WITH RESTRICTIONS
- NOT APPROVED

Justify the decision in detail.

=====================================================
IMPORTANT RULES
=====================================================

- DO NOT assume implementation is correct
- Be extremely critical
- Identify edge cases
- Challenge architectural decisions
- Validate operational readiness
- Validate governance compliance
- Validate supportability
- Validate maintainability
- Validate long-term scalability

If something is ambiguous:
- explicitly flag it as ambiguity
- explain the risk
- recommend remediation

Your audit must behave like:
- enterprise architecture review
- security review
- operational readiness review
- production readiness assessment
- governance compliance assessment

Be exhaustive.
Be skeptical.
Be technically rigorous.