# UK Estate Agent CRM Competitor Analysis

## Methodology

- Reviewed the offline Apex27 application snapshots included in the repository to capture functional highlights around marketing, compliance, workflow, telephony, and finance tooling.
- Used the curated competitor summaries in `docs/competitors.md` as the available secondary research for other UK estate-agent CRM vendors.
- Documented observable strengths and information gaps for each platform based solely on the accessible material, noting where further fact-finding is required for pricing or deeper evaluations.

## Apex27

**Feature highlights**

- Floorplan workspace lets staff upload, resize, and create plans via an embedded creator, indicating integrated floorplan production.【F:Apex27/view lettings property page†L3971-L4045】
- EPC management panel covers lookup, exemptions, ratings, expiry dates, certificate numbers, and notes, demonstrating end-to-end EPC handling inside the CRM.【F:Apex27/view lettings property page†L3696-L3849】
- Marketing tab provides brochure uploads, artwork generation, and link management for print collateral, showcasing customisable print marketing assets.【F:Apex27/view lettings property page†L4050-L4095】
- Financials menu surfaces invoice creation, payment posting, arrears, and rent tracking shortcuts, evidencing invoicing and rent collection workflows.【F:Apex27/add lettings property page†L964-L1009】
- Lettings workspace embeds Twilio-powered calling with tracking numbers, suggesting integrated call tracking and logging.【F:Apex27/lettings page†L186-L205】【F:Apex27/lettings page†L1751-L1989】
- Admin centre exposes dedicated workflow configuration alongside onboarding and compliance checklists, highlighting configurable workflows and check automation.【F:Apex27/admin panel page†L1320-L1358】
- Header includes an update indicator to signal when new releases are available, implying regular SaaS updates distributed to users.【F:Apex27/dashboard†L80-L82】

**Pros**

- Broad operational coverage across marketing, compliance, finance, telephony, and automation modules reduces the need for third-party add-ons.【F:Apex27/view lettings property page†L3696-L4095】【F:Apex27/add lettings property page†L964-L1009】【F:Apex27/admin panel page†L1320-L1358】
- Embedded EPC tooling and brochure workflows keep regulatory and marketing artefacts in one place, streamlining agency compliance and branding tasks.【F:Apex27/view lettings property page†L3696-L4095】

**Cons / Considerations**

- The volume of options and configuration toggles within marketing and EPC sections could create a steeper onboarding curve for new staff without guided workflows.【F:Apex27/view lettings property page†L3696-L4095】
- Financial shortcuts emphasise manual navigation between many subsections, suggesting potential complexity for agencies seeking a simplified rent and payment dashboard.【F:Apex27/add lettings property page†L964-L1009】

## Street.co.uk

**Feature highlights**

- Positioned as a modern CRM focused on automation plus dedicated vendor and buyer apps, pointing to strong client-facing mobile experiences.【F:docs/competitors.md†L30-L34】
- Digital onboarding focus implies streamlined data capture for new customers, differentiating Street on workflow automation.【F:docs/competitors.md†L30-L34】

**Pros**

- Automation and client app coverage align with agencies prioritising self-service journeys and always-on communication.【F:docs/competitors.md†L30-L34】

**Cons / Considerations**

- Available summary does not outline accounting or rent management tooling, so due diligence is required to confirm financial feature depth.【F:docs/competitors.md†L30-L34】
- Pricing information is absent from the provided overview, leaving cost transparency unclear until further research is completed.【F:docs/competitors.md†L30-L34】

## AgentPro

**Feature highlights**

- Offers both desktop and cloud delivery while covering sales, lettings, and property management, suggesting flexibility for hybrid agency setups.【F:docs/competitors.md†L45-L48】

**Pros**

- Dual deployment options let agencies migrate at their own pace without abandoning on-premises workflows immediately.【F:docs/competitors.md†L45-L48】

**Cons / Considerations**

- Summary omits details on integrations or mobile access, signalling a research gap before assuming parity with cloud-native rivals.【F:docs/competitors.md†L45-L48】
- No pricing context is present in the available description, so budgeting impact remains unknown.【F:docs/competitors.md†L45-L48】

## Additional UK Competitors from Repository Research

| Vendor | Noted Positioning | Observed Strengths | Information Gaps |
| --- | --- | --- | --- |
| Reapit | Cloud CRM for sales, lettings, and client accounting.【F:docs/competitors.md†L5-L13】 | Unified CRM and accounting coverage appeals to multi-branch agencies.【F:docs/competitors.md†L5-L13】 | Pricing, automation depth, and mobile tooling not documented in the summary.【F:docs/competitors.md†L5-L13】 |
| Alto | End-to-end software with portal publishing, marketing, and client management.【F:docs/competitors.md†L10-L13】 | Built-in portal syndication supports rapid marketing.【F:docs/competitors.md†L10-L13】 | No insight into automation or API ecosystem from the available note.【F:docs/competitors.md†L10-L13】 |
| Dezrez (Rezi) | Web-based CRM with integrations, automation, and websites.【F:docs/competitors.md†L15-L18】 | Automation plus website services provide an end-to-end stack.【F:docs/competitors.md†L15-L18】 | Pricing and accounting coverage not listed.【F:docs/competitors.md†L15-L18】 |
| AgentOS | Cloud platform with diary, accounting, and partner integrations.【F:docs/competitors.md†L20-L23】 | Wide integration ecosystem can extend functionality quickly.【F:docs/competitors.md†L20-L23】 | Mobile app availability and pricing need verification.【F:docs/competitors.md†L20-L23】 |
| Gnomen | Web software with built-in CMS, property management, and marketing tools.【F:docs/competitors.md†L25-L28】 | Integrated CMS differentiates for agencies needing websites bundled with CRM.【F:docs/competitors.md†L25-L28】 | Support model and automation capabilities unspecified.【F:docs/competitors.md†L25-L28】 |
| PCHomes | Multi-branch sales, lettings, management, and accounts coverage.【F:docs/competitors.md†L35-L38】 | Strong fit for agencies requiring accounting plus branch scalability.【F:docs/competitors.md†L35-L38】 | No mention of client apps or automation features in the summary.【F:docs/competitors.md†L35-L38】 |
| Acquaint CRM | Property marketing, diary, and client management toolkit.【F:docs/competitors.md†L40-L43】 | Core CRM essentials consolidated in one platform.【F:docs/competitors.md†L40-L43】 | Integrations, automation, and pricing remain undocumented.【F:docs/competitors.md†L40-L43】 |
| Veco by Eurolink | Workflow automation, accounts, and API integrations.【F:docs/competitors.md†L50-L53】 | API access and workflow automation appeal to enterprise agencies.【F:docs/competitors.md†L50-L53】 | Pricing tiers and mobile capabilities not captured in source note.【F:docs/competitors.md†L50-L53】 |

## Data Gaps for Priority Competitors

- The offline research pack does not include profiles for 10Ninety, Lifesycle by Iceberg Digital, or Expert Agent, so fresh desk research is still needed to benchmark their automation, marketing, and pricing models.

## Recommendations for Ressapp

- Deliver unified sales, lettings, and accounting workflows with strong portal integrations to match Reapit and PCHomes while staying competitive on operational breadth.【F:docs/competitors.md†L57-L63】
- Invest in automation, workflow tooling, and client-facing apps to rival Street and Dezrez, ensuring agencies can deploy modern digital journeys.【F:docs/competitors.md†L59-L62】
- Maintain open APIs and partner integrations similar to AgentOS while highlighting responsive support and onboarding to stand out where competitors provide limited transparency.【F:docs/competitors.md†L60-L63】

## Implementation Progress

- Introduced a unified dashboard service and API endpoint that aggregates sales, lettings, accounting, portal distribution, and active partner integration metrics so agencies receive a single snapshot of operations.【F:app/Services/UnifiedAgencyDashboardService.php†L7-L100】【F:app/Http/Controllers/Api/DashboardApiController.php†L7-L19】【F:routes/api.php†L9-L33】
- Expanded workflow automation to detect property status transitions, create contextual follow-up tasks, trigger portal synchronisation jobs, and dispatch partner webhooks, aligning Ressapp with the automation focus highlighted by Street and Dezrez.【F:app/Providers/AppServiceProvider.php†L7-L53】【F:app/Services/WorkflowEngine.php†L9-L170】【F:app/Jobs/SyncPropertyToPortal.php†L7-L32】
- Added partner integration records, management APIs, and migrations so agencies can configure third-party portals or automation providers in line with AgentOS-style ecosystems.【F:app/Models/PartnerIntegration.php†L7-L28】【F:app/Http/Controllers/Api/PartnerIntegrationController.php†L7-L52】【F:database/migrations/2025_10_11_020500_create_partner_integrations_table.php†L1-L22】

