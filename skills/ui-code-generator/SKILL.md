---
name: ui-code-generator
version: 1.0.0
description: >
  Multi-purpose UI/UX analysis and code generation skill for frontend/backend developers.
  Analyzes design mockups (PNG) → design specifications, generates production-grade
  HTML/CSS/JavaScript/PHP code with WCAG 2.2 AA accessibility, ITCSS architecture,
  7-tier responsive breakpoints, and CoreMusic rule injection. Supports design-to-code
  workflows for any project (CoreMusic + general use).
category: Frontend/Full-stack
author: Claude Haiku 4.5
created: 2026-06-11
triggers:
  - "ui analysis"
  - "code generation"
  - "design to code"
  - "mockup"
  - "web dev"
  - "html css js"
  - "responsive design"
  - "accessibility"
target_users: "Frontend developers, full-stack engineers, UI/UX designers"
domain: [SOFTWARE_ENGINEERING, UI_UX, SECURITY]
tags: [design, responsive, accessibility, wcag, css-grid, html5, javascript, php]
---

# UI Code Generator Skill

## Overview

**Two-Mode Workflow:**

1. **Mode 1: UI/UX Analysis** — PNG mockup → design specification (Markdown)
2. **Mode 2: Code Generation** — Design spec → HTML/CSS/JS/PHP code files

**Key Features:**
- ✅ WCAG 2.2 AA accessibility compliance
- ✅ ITCSS CSS architecture (7 layers)
- ✅ CSS Grid responsive layout (7 breakpoints: 320px-3840px)
- ✅ Design tokens with CSS custom properties (`--*-*`)
- ✅ Mobile-first approach with min-width media queries
- ✅ Security-first code (OWASP Top 10:2025, PDO, XSS/CSRF mitigation)
- ✅ CoreMusic rule injection (optional)
- ✅ Lighthouse 95+ performance targets
- ✅ Vanilla ES6 JavaScript (no frameworks)
- ✅ Semantic HTML5 with ARIA labels

---

## Activation Triggers

```
Keyword Detection (Trigger Phrases):
- "ui analysis" / "analyze this mockup"
- "code generation" / "generate code"
- "design to code" / "d2c"
- "mockup" / "design mockup"
- "web dev" / "frontend code"
- "html css js" / "responsive"
- "accessibility" / "wcag"
- "create component" / "build layout"
```

When user mentions any trigger, skill activates and enters **Mode Selection** workflow.

---

## Mode 1: UI/UX Analysis Workflow

### Input
- PNG mockup image (design mockup)
- OR reference to design specification (home-3840.md, design-tokens.md)

### Process

1. **Visual Analysis**
   - Extract color palette (hex values, CSS vars)
   - Identify typography (font-family, sizes, weights, line-height)
   - Map spacing system (padding, margin, gaps)
   - Detect layout grid (columns, rows, alignment)
   - Identify components (buttons, cards, forms, hero, footer)

2. **Responsive Breakpoint Analysis**
   - Identify breakpoint transitions in mockup
   - Map to 7-tier standard (see Breakpoint Strategy section)
   - Document layout changes per breakpoint
   - Check mobile-first flow (min-width queries)

3. **WCAG 2.2 AA Compliance Check**
   - Color contrast analysis (4.5:1 minimum)
   - Touch target sizing (24×24px minimum)
   - Focus outline visibility (3px minimum)
   - Semantic structure assessment
   - Keyboard navigation review
   - Screen reader accessibility (ARIA labels)

4. **Component Library Extraction**
   - Button styles (default, hover, active, disabled, loading)
   - Card variations (image, text, interactive)
   - Form elements (input, select, textarea, checkbox, radio)
   - Navigation (header, breadcrumb, footer)
   - Typography hierarchy (h1-h6, body, caption, code)
   - Special components (modal, toast, sidebar, hero)

### Output: Design Specification (Markdown)

```markdown
# Design Specification: [Project Name]

## Color Palette
| Name | Hex | CSS Variable | Usage |
|------|-----|--------------|-------|
| Primary | #9d4edd | --color-primary | buttons, links |
| ... | ... | ... | ... |

## Typography System
| Level | Font | Size | Weight | Line Height |
|-------|------|------|--------|-------------|
| H1 | AvalonMedium | 48px | 600 | 1.2 |
| ... | ... | ... | ... | ... |

## Spacing System
- Base unit: 8px
- Scale: 4, 8, 12, 16, 24, 32, 48, 64px

## Layout Grid
- 12-column grid at all breakpoints
- Gap: 16-40px (responsive)
- Container max-width: varies per breakpoint

## Breakpoint Strategy
[7 tiers documented with min-width values]

## Component Library
[40+ components with state matrix]

## WCAG 2.2 AA Compliance Checklist
[✅ or ❌ for each success criterion]

## Responsive Behavior
[Layout changes per breakpoint]

## Performance Targets
- Lighthouse: 95+
- FCP: <1.0s
- LCP: <1.5s
```

---

## Mode 2: Code Generation Workflow

### Input
- Design specification (Markdown) OR
- PNG mockup + user description

### Output Files

#### HTML (Semantic, Accessible)
```
generated-{project}/
├── index.html                  ← Full page with semantic sections
├── components/
│   ├── header.html             ← Reusable header component
│   ├── button.html             ← Button variations
│   ├── card.html               ← Card component template
│   ├── form.html               ← Form with labels + validation
│   └── footer.html             ← Footer with links
└── pages/
    └── [page-name].html        ← Full pages (home, about, contact, etc.)
```

**HTML Standards:**
- ✅ Semantic HTML5: `<header>`, `<nav>`, `<main>`, `<section>`, `<article>`, `<footer>`
- ✅ ARIA labels for interactive elements
- ✅ Heading hierarchy (h1-h6, single h1 per page)
- ✅ Form labels with `<label for="">` association
- ✅ Alt text on images
- ✅ Skip navigation link
- ✅ Language attribute `<html lang="tr">` or `<html lang="en">`

#### CSS (ITCSS, CSS Grid, Custom Properties)
```
generated-{project}/css/
├── 01_Abstracts/
│   ├── _variables.css          ← CSS custom properties (--color-*, --font-*, --space-*)
│   ├── _breakpoints.css        ← Media query helpers (@mixin concept)
│   └── _functions.scss         ← Utility functions (if SCSS)
├── 02_Base/
│   ├── _reset.css              ← Normalize + custom reset
│   └── _typography.css         ← Font faces, base text styles
├── 03_Layout/
│   ├── _grid.css               ← 12-column CSS Grid system
│   ├── _flexbox.css            ← Flex utilities
│   ├── _header.css             ← Header sticky/fixed layout
│   ├── _footer.css             ← Footer layout
│   └── _sidebar.css            ← Sidebar collapse logic
├── 04_Components/
│   ├── _button.css             ← Button states
│   ├── _card.css               ← Card layouts
│   ├── _form.css               ← Form styling
│   ├── _navigation.css         ← Nav bar styles
│   └── _modal.css              ← Modal overlay
├── 05_Pages/
│   ├── _home.css               ← Home page overrides
│   ├── _about.css              ← About page overrides
│   └── _contact.css            ← Contact page overrides
├── 06_Utilities/
│   ├── _responsive.css         ← Responsive helpers (.hide-mobile, .show-tablet)
│   ├── _accessibility.css      ← A11y utilities (.sr-only, focus-visible)
│   └── _animations.css         ← Keyframe animations
├── 07_Vendors/
│   └── _external.css           ← Third-party library overrides
└── main.css                    ← Master import file
```

**CSS Standards:**
- ✅ ITCSS methodology (layers 1-7)
- ✅ CSS Grid for page layout, Flexbox for components
- ✅ CSS custom properties throughout (`var(--color-primary)`)
- ✅ Mobile-first: base styles for 320px, then `@media (min-width: 768px)`
- ✅ No magic numbers (use variables)
- ✅ Modular components (single responsibility)
- ✅ Focus outlines for keyboard navigation (3px, visible)
- ✅ Reduced motion respect (`@media (prefers-reduced-motion)`

#### JavaScript (Vanilla ES6, No Frameworks)
```
generated-{project}/js/
├── utils/
│   ├── dom.js                  ← DOM manipulation helpers
│   ├── events.js               ← Event listener management
│   ├── responsive.js           ← Breakpoint detection
│   └── accessibility.js        ← A11y helpers (focus trap, skip nav)
├── components/
│   ├── Button.js               ← Button interactive behavior
│   ├── Modal.js                ← Modal show/hide + focus management
│   ├── Navigation.js           ← Mobile menu toggle
│   ├── Form.js                 ← Form validation + submission
│   └── Slider.js               ← Carousel/slider component
├── pages/
│   ├── home.js                 ← Home page initialization
│   ├── about.js                ← About page behaviors
│   └── contact.js              ← Contact form handler
└── main.js                     ← App initialization, event delegation
```

**JavaScript Standards:**
- ✅ No `var`, only `const`/`let`
- ✅ async/await (no callbacks/promises in examples)
- ✅ AbortController for fetch calls
- ✅ Event listener cleanup on component destroy
- ✅ No inline event handlers (use addEventListener with delegated events)
- ✅ CSS class toggling (no inline styles)
- ✅ Responsive behavior (detect breakpoint, adjust layout)
- ✅ Accessibility-first (focus management, ARIA updates)

#### PHP (Optional, Form Handlers)
```
generated-{project}/api/
├── config/
│   ├── config.php              ← Database, env config
│   └── security.php            ← OWASP headers, CSRF tokens
├── handlers/
│   ├── ContactFormHandler.php  ← Contact form submission
│   ├── LoginHandler.php        ← Login form handler
│   └── RegisterHandler.php     ← Registration handler
└── utilities/
    ├── Sanitizer.php           ← Input validation + sanitization
    ├── CsrfToken.php           ← CSRF token generation + validation
    └── Logger.php              ← Structured logging
```

**PHP Standards (CoreMusic mode):**
- ✅ `declare(strict_types=1)` at file start
- ✅ PDO prepared statements (no raw SQL)
- ✅ Input validation + sanitization
- ✅ CSRF token validation
- ✅ Output encoding (htmlspecialchars)
- ✅ Secure headers (CSP, HSTS, X-Frame-Options)
- ✅ Error handling (no stack traces to user)
- ✅ Structured logging with trace IDs

---

## CoreMusic Rule Injection

When user selects **"Yes, CoreMusic project"** during setup:

### PHP Rules
```php
declare(strict_types=1);           // Every file
$stmt = $pdo->prepare(...);        // No raw SQL
hash_equals($token, $input);       // Timing-safe comparison
password_hash($pwd, PASSWORD_ARGON2ID);  // Secure hashing
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));  // 256-bit tokens
```

### CSS Rules
```css
/* ITCSS structure mandatory */
/* --cm-* prefix for all variables */
/* WCAG 2.2 AA minimum */
/* 7 breakpoints required */
```

### JavaScript Rules
```js
const fetchData = async (url) => {
  const controller = new AbortController();  // Mandatory
  // ... cleanup on unmount
  controller.abort();  // Prevent stale responses
};
```

### Security Rules (OWASP Top 10:2025)
- ✅ XSS: Output encoding, CSP nonce
- ✅ CSRF: Token validation, SameSite cookies
- ✅ SQL Injection: Parameterized queries only
- ✅ Authentication: Secure session management
- ✅ File Upload: Type validation, secure storage
- ✅ Security Headers: CSP, HSTS, X-Frame-Options

---

## Quality Control Checklist (40+ Items)

### Layout & Responsiveness
- [ ] 7 breakpoints tested (320px, 600px, 768px, 1024px, 1280px, 1920px, 2560px+)
- [ ] CSS Grid used for main layout
- [ ] Flexbox used for component layout
- [ ] Mobile-first approach (base styles at 320px)
- [ ] No horizontal scrolling at any breakpoint
- [ ] Images responsive (max-width: 100%)
- [ ] Touch-friendly spacing (24×24px minimum targets)

### Accessibility (WCAG 2.2 AA)
- [ ] Color contrast ≥ 4.5:1 for text/background
- [ ] Focus outlines visible (3px, contrasting color)
- [ ] Keyboard navigation works (Tab through all interactive elements)
- [ ] Semantic HTML (proper heading hierarchy, landmarks)
- [ ] ARIA labels on icons, buttons, form inputs
- [ ] Alt text on all meaningful images
- [ ] Form labels associated with inputs
- [ ] Error messages linked to form fields

### CSS Quality
- [ ] ITCSS structure (7 layers, imports in correct order)
- [ ] All colors use CSS custom properties (`var(--*)`)
- [ ] All spacing uses CSS custom properties
- [ ] No magic numbers (use variables)
- [ ] No !important except in utilities
- [ ] Reusable component classes (single responsibility)
- [ ] Media queries mobile-first (min-width only)
- [ ] Cross-browser tested (Chrome, Firefox, Safari, Edge)

### JavaScript Quality
- [ ] No `var` declarations (const/let only)
- [ ] No TypeScript `any` types (if TypeScript used)
- [ ] All fetch calls use AbortController
- [ ] Event listeners cleaned up on unmount
- [ ] No inline event handlers (event delegation used)
- [ ] Async/await (no callback hell)
- [ ] No global variables (module scope or closures)
- [ ] Error handling for async operations

### PHP Security (if CoreMusic mode)
- [ ] `declare(strict_types=1)` in every file
- [ ] All SQL queries use PDO prepared statements
- [ ] Input validation on every `$_GET`/`$_POST`/`$_FILES`
- [ ] Output encoding with `htmlspecialchars()`
- [ ] CSRF token validation on form submissions
- [ ] `hash_equals()` for token comparison
- [ ] No hardcoded credentials
- [ ] Error messages don't leak system information

### Performance (Lighthouse)
- [ ] Lighthouse score ≥ 95
- [ ] First Contentful Paint (FCP) < 1.0s
- [ ] Largest Contentful Paint (LCP) < 1.5s
- [ ] Cumulative Layout Shift (CLS) < 0.05
- [ ] Total Blocking Time (TBT) < 100ms
- [ ] No render-blocking CSS/JS
- [ ] Images optimized (WebP, appropriate sizes)
- [ ] CSS/JS minified and gzipped

### Code Organization
- [ ] HTML: Semantic structure, no divitis
- [ ] CSS: Modular files, clear naming
- [ ] JavaScript: Separated concerns, reusable functions
- [ ] No dead code or commented-out code
- [ ] Consistent naming conventions
- [ ] Clear file/folder structure

---

## Persona Detection (JavaScript)

For projects with persona modes (HOME/PROFESSIONAL/STUDIO):

```javascript
const userTier = localStorage.getItem('cm_user_tier') || 'home';

const showWidgetsForTier = (tier) => {
  document.querySelectorAll('[data-persona]').forEach(el => {
    const required = el.dataset.persona;
    const show = {
      'home': ['home'],
      'professional': ['home', 'professional'],
      'studio': ['home', 'professional', 'studio']
    }[tier].includes(required);
    
    el.style.display = show ? 'block' : 'none';
  });
};

showWidgetsForTier(userTier);
```

---

## 7-Tier Breakpoint Strategy

```css
/* Mobile First: Base 320px */
body { font-size: 16px; }

/* Mobile Wide: 600px */
@media (min-width: 600px) {
  body { font-size: 18px; }
}

/* Tablet: 768px */
@media (min-width: 768px) {
  main { display: grid; grid-template-columns: 1fr 1fr; }
}

/* Tablet Wide: 1024px */
@media (min-width: 1024px) {
  main { grid-template-columns: 1fr 1fr 1fr; }
}

/* Desktop: 1280px */
@media (min-width: 1280px) {
  .sidebar { width: 240px; }
}

/* Desktop Wide: 1920px (Base design)*/
@media (min-width: 1920px) {
  .container { max-width: 1400px; }
}

/* TV/4K: 2560px+ */
@media (min-width: 2560px) {
  body { font-size: 20px; }
  .container { max-width: 2000px; }
}
```

---

## References & Resources

For detailed implementation guides, see `references/` folder:
- `design-analysis.md` — Step-by-step UI mockup analysis
- `wcag-2.2-checklist.md` — Accessibility validation procedures
- `css-architecture.md` — ITCSS deep dive with examples
- `responsive-breakpoints.md` — Mobile-first breakpoint strategy
- `code-templates.md` — Reusable HTML/CSS/JS snippets
- `coremusic-rules.md` — CoreMusic-specific security + architecture
- `anti-patterns.md` — Common mistakes and corrections

---

## Skill Status

✅ **Production Ready** — Tested on home-3840.md, home-dashboard.md  
✅ **WCAG 2.2 AA Compliant** — Accessibility-first approach  
✅ **OWASP Top 10:2025 Secure** — Security validation included  
✅ **Performance Optimized** — Lighthouse 95+ targets  
✅ **CoreMusic Integrated** — Optional rule injection  

---

**Version:** 1.0.0  
**Last Updated:** 2026-06-11  
**Status:** Active
