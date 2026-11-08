# 08 — Design System: Novixa WorkOS

## 1. Visual Philosophy: The Focused B2B Workspace

Novixa WorkOS embodies an aesthetic of **quiet confidence, high information density, and functional precision**.
Taking inspiration from modern engineering instruments (Linear, Plane, Notion, Raycast), the interface eliminates superfluous decorative elements (gradients, neon glows, floaty cards) in favor of clear visual hierarchy, crisp typography, consistent borders, and intentional spacing.

---

## 2. Color Palette & Semantic Tokens

### 2.1 Brand & Accent Colors
* **Novixa Primary Blue:** `#2563EB` (Tailwind `blue-600`) — Primary actions, active navigation states, key selection indicators.
* **Deep Navy Base:** `#0F172A` (Tailwind `slate-900`) & `#020617` (Tailwind `slate-950`) — Deep workspace surface, dark mode backgrounds.
* **Accent Teal:** `#14B8A6` (Tailwind `teal-500`) — Success states, finished milestones, verified tags.

### 2.2 Neutral Surfaces & Borders (Dark Mode First-Class, Light Mode Supported)
```css
:root {
  /* Light Mode */
  --bg-canvas: #F8FAFC;       /* Slate-50 */
  --bg-surface: #FFFFFF;      /* Pure White */
  --bg-subtle: #F1F5F9;       /* Slate-100 */
  --border-subtle: #E2E8F0;   /* Slate-200 */
  --border-strong: #CBD5E1;   /* Slate-300 */
  --text-primary: #0F172A;    /* Slate-900 */
  --text-secondary: #475569;  /* Slate-600 */
  --text-muted: #94A3B8;      /* Slate-400 */
}

.dark {
  /* Dark Mode */
  --bg-canvas: #090D16;       /* Deepest Slate */
  --bg-surface: #0F172A;      /* Slate-900 */
  --bg-subtle: #1E293B;       /* Slate-800 */
  --border-subtle: #1E293B;   /* Slate-800 */
  --border-strong: #334155;   /* Slate-700 */
  --text-primary: #F8FAFC;    /* Slate-50 */
  --text-secondary: #94A3B8;  /* Slate-400 */
  --text-muted: #64748B;      /* Slate-500 */
}
```

### 2.3 Status & Priority Colors
* **Urgent / Blocker / Overdue:** Red (`#EF4444` / `rose-500`)
* **High Priority / In Progress:** Amber (`#F59E0B` / `amber-500`)
* **Medium Priority / Planning:** Blue (`#3B82F6` / `blue-500`)
* **Low Priority / Backlog:** Slate (`#64748B` / `slate-500`)
* **Completed / Won:** Emerald (`#10B981` / `emerald-500`)

---

## 3. Typography & Bidirectional Font Selection

To achieve typographic harmony between Arabic and English:
1. **Arabic Primary Font:** **IBM Plex Sans Arabic** / **Readex Pro** / **Cairo**.
   - Balanced x-height that aligns naturally with Latin characters.
   - Clean geometric letterforms that preserve legibility at 12px-14px UI scale.
2. **English & Numerals:** **Inter** paired with tabular figures (`tabular-nums`) for currency and dates.
3. **Monospace / Code / IDs:** **JetBrains Mono** or `ui-monospace` for task codes (e.g. `NW-104`).

### Type Scale
- `Display` (Page Headers): 20px / 1.25rem, SemiBold (600)
- `Heading` (Section Titles): 16px / 1rem, SemiBold (600)
- `Body` (Standard Text): 14px / 0.875rem, Regular (400) & Medium (500)
- `Caption / Meta` (Badges, Timestamps, Labels): 12px / 0.75rem, Medium (500)
- `Micro` (Keyboard shortcuts, status pills): 11px / 0.6875rem, Medium (500)

---

## 4. Spacing, Elevation & Radii

- **Border Radius:**
  - Micro controls & badges: `rounded-md` (6px)
  - Cards & modals: `rounded-lg` (8px)
  - Full pill badges: `rounded-full` (9999px)
- **Shadows:** Minimal, subtle elevation. No heavy blurry dropshadows:
  - Surface: `shadow-sm`
  - Floating menus & command palette: `shadow-lg border border-slate-200 dark:border-slate-800`
- **Iconography:** Cohesive **Lucide Icons** exclusively. Never use colorful emojis as primary interface navigation or status icons.

---

## 5. Bidirectional (RTL / LTR) Layout Rules

1. Always use CSS logical properties via Tailwind:
   - `ms-*` (margin-inline-start) instead of `ml-*`
   - `me-*` (margin-inline-end) instead of `mr-*`
   - `ps-*` (padding-inline-start) instead of `pl-*`
   - `pe-*` (padding-inline-end) instead of `pr-*`
   - `start-*` and `end-*` for absolute positioning.
2. Direction-aware icons:
   - Arrows indicating sequence progression (`ChevronRight` in LTR becomes `ChevronLeft` in RTL).
   - Icons representing physical objects (magnifying glass, clock, calendar, checkmark) remain un-mirrored.
3. Numerals and Code IDs:
   - Task codes like `NW-042` and phone numbers `+967 776 248 526` maintain `dir="ltr"` inline formatting to avoid punctuation misplacement.
