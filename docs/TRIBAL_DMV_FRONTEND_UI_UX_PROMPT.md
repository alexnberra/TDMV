# Tribal DMV Platform - Frontend UI/UX Implementation Prompt (Laravel + Inertia + Vue 3 + shadcn-vue + Tailwind)

Use this prompt as-is with Codex.

## Mission
Implement a complete frontend UI/UX redesign for the Tribal DMV platform while preserving all existing backend features, models, endpoints, and workflows.

## Critical Constraints
1. Do not change database models, migrations, backend business rules, API response contracts, or route behavior.
2. Do not remove or alter existing member/staff/admin features.
3. Keep Laravel as the single source of truth for routing and data.
4. Use Inertia pages for navigation. Do not introduce Vue Router.
5. Keep frontend state minimal and page-scoped unless truly shared.
6. Reuse existing API client and composables in `resources/js/lib/api.ts` and `resources/js/composables`.
7. Use shadcn-vue components plus Tailwind utilities for styling.
8. Preserve existing feature surfaces:
- Marketing pages
- Member portal pages
- Admin dashboard
- Fleet/compliance workflows
- Household/appointments workflows
- AI assistant/automation workflows

## Existing Project Context
Current frontend structure (Laravel Inertia):
1. `resources/js/app.ts`
2. `resources/js/layouts/PortalLayout.vue`
3. `resources/js/layouts/MarketingLayout.vue`
4. `resources/js/pages/marketing/*`
5. `resources/js/pages/portal/*`
6. `resources/js/lib/api.ts`
7. `resources/js/composables/useApiAuth.ts`
8. `resources/css/app.css`
9. Laravel routes in `routes/web.php` and `routes/api.php`

Existing portal pages to redesign (same functionality, improved UX):
1. `Dashboard.vue`
2. `ServiceSelector.vue`
3. `RequirementsChecklist.vue`
4. `DocumentUpload.vue`
5. `ReviewPayment.vue`
6. `ApplicationStatus.vue`
7. `VehicleProfile.vue`
8. `Notifications.vue`
9. `Support.vue`
10. `AdminDashboard.vue`
11. `Phase2aOps.vue` (Business, Fleet & Compliance)
12. `Phase2bOps.vue` (Households & Appointments)
13. `Phase3Ops.vue` (AI Assistant & Automation)

## Design Direction
Deliver a modern, trustworthy civic platform aesthetic:
1. Clean spacing and hierarchy
2. High readability and strong contrast
3. Mobile-first touch-friendly controls (44px+ hit targets)
4. Progressive disclosure in complex forms
5. Minimal cognitive load with clear status and next actions
6. “Financial-grade trust” visual language

## Design Tokens (Required)
Implement these tokens via CSS variables in `resources/css/app.css` and consume through Tailwind utility classes.

```css
:root {
  --color-blue-50: #eff6ff;
  --color-blue-100: #dbeafe;
  --color-blue-200: #bfdbfe;
  --color-blue-300: #93c5fd;
  --color-blue-400: #60a5fa;
  --color-blue-500: #3b82f6;
  --color-blue-600: #2563eb;
  --color-blue-700: #1d4ed8;
  --color-blue-800: #1e40af;
  --color-blue-900: #1e3a8a;

  --color-tribal-primary: #d97706;
  --color-tribal-secondary: #92400e;
  --color-tribal-light: #fef3c7;

  --color-success-500: #22c55e;
  --color-warning-500: #f97316;
  --color-error-500: #ef4444;

  --color-gray-50: #f9fafb;
  --color-gray-100: #f3f4f6;
  --color-gray-200: #e5e7eb;
  --color-gray-300: #d1d5db;
  --color-gray-400: #9ca3af;
  --color-gray-500: #6b7280;
  --color-gray-600: #4b5563;
  --color-gray-700: #374151;
  --color-gray-800: #1f2937;
  --color-gray-900: #111827;

  --radius-sm: 4px;
  --radius-base: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-xl: 20px;
  --radius-2xl: 24px;
}
```

Typography:
1. Primary stack: `-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif`
2. Base body minimum: `16px`
3. Strong heading hierarchy from `text-xl` through `text-5xl` and above where appropriate

Spacing:
1. Use an 8px rhythm
2. Keep page sections visually separated
3. Avoid cramped cards/forms

Motion:
1. Default transitions 150ms-250ms
2. Respect `prefers-reduced-motion`

## Component Requirements (shadcn-vue first)
Use shadcn-vue primitives and variants consistently:
1. `Button`
2. `Input`
3. `Label`
4. `Card`
5. `Badge`
6. `Alert`
7. `Dialog`
8. `Tabs`
9. `Accordion`
10. `Select`
11. `Checkbox`
12. `RadioGroup`
13. `Switch`
14. `Progress`
15. `Table`
16. `Avatar`
17. `Popover`
18. `DropdownMenu`
19. `Sonner` toast usage

Create reusable domain components under `resources/js/components/portal`:
1. `VehicleCard.vue`
2. `StatusChip.vue`
3. `ChecklistItem.vue`
4. `TimelineStep.vue`
5. `ActionCard.vue`
6. `DocumentUploadTile.vue`
7. `FeeSummaryCard.vue`

Keep props strongly typed and avoid hidden side effects.

## Layout Requirements
### Marketing Layout
1. Sticky/fixed top nav with clear CTA hierarchy
2. Strong hero section
3. Feature cards and trust-building sections
4. Accessible footer with legal/contact links

### Portal Layout
1. Desktop left sidebar
2. Mobile bottom nav
3. Persistent identity/account area
4. Responsive content region with max width
5. Respect current navigation labels:
- Home
- Services
- Fleet & Compliance
- Households & Appointments
- AI Assistant & Automation
- Checklist
- Uploads
- Vehicle 2
- Alerts
- Help
- Admin (staff/admin only)

## Page-by-Page UX Expectations
Keep page routes and behavior identical. Upgrade only UI/UX.

### Marketing Pages
1. `HomePage.vue` with hero, benefits, trust and CTA sections
2. `FeaturesPage.vue` with grouped capabilities and visual hierarchy
3. `PricingPage.vue` with transparent pricing card and fee clarity
4. `AboutPage.vue` and `ContactPage.vue` with polished typography and form UX
5. `LoginPage.vue` and `RegisterPage.vue` with clear errors, validation states, and loading states

### Portal Core
1. `Dashboard.vue` with:
- Status summary
- Primary actions
- Vehicle cards
- Recent applications
- Alerts/action-needed components
2. `ServiceSelector.vue` as guided intake
3. `RequirementsChecklist.vue` with progress + clear completion states
4. `DocumentUpload.vue` with mobile camera-friendly UX and upload states
5. `ReviewPayment.vue` with transparent fee breakdown + terms confirmation
6. `ApplicationStatus.vue` with timeline visualization and action-needed banners
7. `VehicleProfile.vue` with status and expiration emphasis
8. `Notifications.vue` grouped by unread/read with robust filtering states
9. `Support.vue` with FAQ/search/contact/locations hierarchy

### Admin and Advanced Pages
1. `AdminDashboard.vue` improved scanning and actions
2. `Phase2aOps.vue` visually grouped by business/fleet/compliance
3. `Phase2bOps.vue` visually grouped by household/appointment tasks
4. `Phase3Ops.vue` polished assistant + automation + insight panels

## Form and Validation UX Standards
1. Validate on blur and on submit
2. Show clear inline error text and visual field states
3. Disable submit during async operations
4. Show loading indicators for mutations
5. Preserve entered values on error
6. Provide success toasts/messages on completion

## Loading, Empty, Error, and Success States
Implement explicit states on each major page:
1. Skeleton loading UI where data-heavy
2. Empty states with action CTA
3. Error states with retry
4. Success confirmations after key actions

## Accessibility Requirements (must pass)
1. WCAG 2.1 AA contrast and semantics
2. Keyboard navigable controls and dialogs
3. Visible focus styles
4. Semantic regions (`header`, `nav`, `main`, `aside`, `footer`)
5. ARIA labels where semantics are insufficient
6. Screen-reader-friendly status and error announcements

## Responsive Requirements
1. Mobile first
2. `sm` improve layout density
3. `md` enable richer two-column regions
4. `lg`/`xl` optimize for desktop scanning
5. Tables degrade into mobile card/list patterns where needed

## Performance Requirements
1. Keep bundles lean
2. Use lazy loading where it helps
3. Avoid unnecessary watchers/computed chains
4. Avoid redundant API calls
5. Prefer memoized/computed derivations for expensive transforms

## Implementation Rules
1. Do not change backend models/controllers/policies/migrations/seeders.
2. Do not add Vue Router. Keep Inertia routing.
3. Do not break existing API methods in `resources/js/lib/api.ts`.
4. Keep existing feature names, data meaning, and flow logic unchanged.
5. Refactor UI into reusable components where sensible.
6. Maintain TypeScript safety where already present.

## Acceptance Criteria
1. All existing pages still work with the same functional outcomes.
2. UI is visibly upgraded and consistent across marketing and portal surfaces.
3. Design tokens are consistently applied.
4. Accessibility checks pass for keyboard navigation and focus.
5. Mobile and desktop both feel intentional.
6. No regressions in existing workflows.
7. `npm run lint`, `npm run types`, and `npm run build` pass.

## Required Output From Codex
Return:
1. Summary of what was changed and why
2. File-by-file change list
3. Any new reusable components created
4. Validation output (lint/types/build)
5. Known tradeoffs or follow-up suggestions

## Implementation Order
1. Establish design tokens and base styles
2. Build reusable UI/domain components
3. Upgrade layouts
4. Upgrade marketing pages
5. Upgrade portal pages
6. Upgrade admin/ops pages
7. Add loading/empty/error/success states everywhere
8. Accessibility pass
9. Responsive pass
10. Final validation pass
