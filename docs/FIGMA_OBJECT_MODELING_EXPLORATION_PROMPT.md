# Tribal DMV App Exploration Prompt (Object Modeling + Task Flows)

Paste this entire prompt into Figma AI / FigJam AI to drive exploration.

## Role
You are a senior UX architect and product designer.
Use an object-modeling-first approach and pair it with task flow definition.
Design from two complementary perspectives:
1. Structural perspective (objects, relationships, vocabulary, conventions)
2. Task perspective (primary and alternate flows, decisions, exceptions)

## Product Context
I am designing a Tribal DMV platform with member portal, staff/admin workflows, and service flows such as:
- Registration renewal
- New registration
- Title transfer
- Plate replacement
- Document upload/review
- Payment
- Appointment scheduling
- Status tracking and notifications

## Constraints
- Keep exploration practical and implementation-ready.
- Prioritize clarity, consistency, and trust.
- Keep terminology consistent across objects, actions, labels, and screens.
- Show both primary flows and alternate/exception flows.
- Make mobile-first choices explicit.

## Method (Required)
Follow this sequence exactly.

### 1) Identify Objects (Nouns)
Create an object inventory from the domain.
Start with likely objects and refine:
- Member Account
- Staff Account
- Tribe
- Vehicle
- Application
- Document
- Payment
- Appointment
- Office Location
- Notification
- Household
- Benefit/Placard

For each object, create an object card with 3 zones:
1. Object name (noun)
2. Key attributes (middle)
3. Actions (verbs) (bottom)

### 2) Define Object Relationships
Create narrative relationship statements in this syntax:
`<object> <verb phrase> <object>`

Classify each connection as one of:
- Association
- Aggregation (list/group object)
- Component (dependency; cannot exist meaningfully without parent)
- Inheritance (parent-child)

Include examples like:
- A Member Account submits one or more Applications.
- An Application includes one or more Documents.
- A Payment settles an Application fee.
- A Household may contain one or more Member Accounts.

### 3) Add Actions with Standard Check
For each object, check standard actions:
- Create
- Edit
- Delete
- Search For

Then add domain-specific actions (for example):
- Vehicle: Renew, Transfer, Replace Plate
- Application: Submit, Cancel, Track Status
- Document: Upload, Replace, Delete
- Appointment: Schedule, Reschedule, Cancel

Do not model micro-interactions (sort/filter/expand/collapse) as object actions unless they are core system functions.

### 4) Add Attributes Intentionally
Add key attributes that affect decisions and UX.
Use status/state attributes where needed, for example:
- Application.status: draft, submitted, under_review, approved, rejected, completed
- Document.status: uploaded, accepted, rejected
- Vehicle.registration_status: active, expired, pending

Only include enough attributes to support architecture and flow decisions.

### 5) Finish and Enhance the Object Model
- Group objects by functional clusters (Core Service, Compliance, Communications, Account/Admin).
- Mark signature objects with a visual marker (star).
- Mark future functionality using lighter style/dashed outline.
- Add annotation callouts for permissions/rules (member vs staff/admin).

### 6) Use Object Model to Drive UI Conventions
Derive reusable patterns from object relationships:
- Aggregate objects should share list/grid/table conventions.
- Parent-child objects should share visual language and behavior.
- Signature objects should receive distinctive design treatment.

Check vocabulary consistency:
- Use one term per concept system-wide.
- Remove near-duplicates (for example, "Edit" vs "Update") unless intentionally differentiated.

### 7) Capture Task Flows
For each key service, produce:
1. Task flow diagram (logic-level)
2. Screen flow diagram (screen-level)

Required services:
- Registration renewal
- New registration
- Title transfer
- Plate replacement
- Appointment booking
- Document correction after rejection

For each flow, explicitly include:
- Preconditions
- Primary (happy path)
- Alternate paths (user choices)
- Exception paths (cannot complete goal)

### 8) Flow Diagram Rules
Use standard flow symbols and labels:
- Process step (rectangle)
- Decision (diamond)
- Terminator (rounded rectangle)
- Optional off-page connector

Decision diamonds must show branch drivers clearly.
Examples:
- "Is user logged in?"
- "Are required documents uploaded?"
- "Payment successful?"
- "Staff requested more info?"

### 9) Build Use Case Narratives for Complex Flows
For at least 3 complex flows, provide use case narratives with:
- Overview
- Actor
- Preconditions
- Primary flow
- Alternate flows
- Exceptions

Target complex flows:
- Title transfer with lienholder
- Rejected document resubmission loop
- Appointment cancellation and reschedule with timing rules

### 10) Synthesize Into Screen Exploration
Create low-fidelity screens from the structural + flow outputs:
- Dashboard
- Service selector
- Requirements checklist
- Document upload
- Review + payment
- Status timeline
- Vehicle profile
- Notifications
- Support

For each screen, map visible actions back to object-action pairs.
If a screen action does not map to an object action, flag it for review.

## Required Deliverables in Figma
Create these sections/frames in order:
1. `01_Object_Inventory`
2. `02_Object_Model_Relationships`
3. `03_Actions_And_Attributes_Audit`
4. `04_Signature_Objects_And_Conventions`
5. `05_Task_Flows_Primary_Alt_Exception`
6. `06_Use_Case_Narratives`
7. `07_Screen_Flow_Diagrams`
8. `08_Structural_To_UI_Mapping`
9. `09_Risks_Gaps_Next_Iteration`

## Quality Bar
- The model should be compact but complete enough to guide design and engineering.
- Every key object should have clear actions and meaningful attributes.
- Every key service should include alternate and exception paths.
- Vocabulary should be consistent across model, flows, and screens.
- Output should be actionable for handoff, not just conceptual.

## Final Output Format
At the end, provide:
1. Final object list with relationship statements
2. Top 3 signature objects and why
3. Design convention recommendations derived from model
4. Flow risk list (where failures/edge cases are likely)
5. Immediate next design sprint plan (5 to 8 concrete tasks)
