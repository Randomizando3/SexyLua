# Design System: The Celestial Editorial

## 1. Overview & Creative North Star
**Creative North Star: "The Lunar Metamorphosis"**

This design system rejects the clinical, "boxed-in" layout of traditional social platforms. Instead of a rigid grid of squares, we embrace the **Lunar Metamorphosis**—a philosophy of soft curves, orbital elements, and shifting phases. We are moving away from the "grid of content" toward a "curated gallery." 

By utilizing intentional asymmetry, overlapping circular motifs, and wide-open "breathing rooms," we create an environment that feels exclusive and sophisticated. The interface should feel like a high-end fashion editorial that happens to be interactive. We are not just building a platform; we are building an atmosphere where creators are framed like celestial bodies.

---

## 2. Colors: Tonal Depth & The "No-Line" Rule
The palette is rooted in a deep, seductive `primary` (#ab1155) and a series of luminous `surface` tones. 

### The "No-Line" Rule
**Explicit Instruction:** Designers are prohibited from using 1px solid borders to define sections. Layout boundaries must be achieved through:
- **Tonal Shifts:** Placing a `surface-container-lowest` card against a `surface-container-low` background.
- **Organic Negative Space:** Using the Spacing Scale (`spacing.10` to `spacing.16`) to create mental boundaries.
- **Soft Shadows:** Using the `on-surface` color at 4% opacity to create a "lifted" edge without a line.

### Surface Hierarchy & Nesting
Treat the UI as a series of stacked, semi-transparent sheets of vellum.
1.  **Base Layer:** `surface` (#fbf9fb) - The canvas.
2.  **Section Layer:** `surface-container-low` (#f5f3f5) - Used for sidebar backgrounds or content grouping.
3.  **Active/Floating Layer:** `surface-container-lowest` (#ffffff) - Used for interactive cards or profile headers to make them "pop" against the canvas.

### The "Glass & Gradient" Rule
To elevate beyond the "out-of-the-box" look:
- **Lunar Glass:** Use `surface-container-highest` with 60% opacity and a `24px` backdrop-blur for floating navigation bars or live-chat overlays.
- **Signature Glow:** Apply a subtle linear gradient from `primary` (#ab1155) to `primary-container` (#cc326e) on primary CTAs and active "Live" indicators to provide a sense of inner light.

---

## 3. Typography: The Editorial Voice
We utilize a high-contrast typographic pairing to mirror the sophistication of a premium magazine.

*   **Display & Headlines (Plus Jakarta Sans):** Our "Celestial" font. It is geometric yet warm. Use `display-lg` for profile names and `headline-md` for section titles. The wide tracking in Plus Jakarta Sans conveys authority and elegance.
*   **Body & Labels (Manrope):** Our "Functional" font. It offers exceptional legibility at small sizes. Use `body-md` for captions and `label-sm` for metadata (e.g., "15 minutes ago").

**Hierarchy Tip:** Always maintain at least two scale jumps between a headline and body text (e.g., `headline-sm` next to `body-md`) to ensure clear visual dominance.

---

## 4. Elevation & Depth: Tonal Layering
Traditional shadows are too heavy for this aesthetic. We achieve depth through **Ambient Luminescence**.

*   **The Layering Principle:** Place `surface-container-lowest` elements on top of `surface-container-high` areas. This creates a natural "step" in the UI hierarchy without adding visual noise.
*   **Ambient Shadows:** For "Floating" elements (like a moon-shaped floating action button), use a shadow color derived from `on-surface` (#1b1c1d). 
    *   *Spec:* `0px 20px 40px rgba(27, 28, 29, 0.06)`.
*   **The "Ghost Border" Fallback:** If a separator is required for accessibility, use `outline-variant` (#e3bdc3) at **15% opacity**. It should be a hint of a line, not a boundary.
*   **Circular Geometry:** Reference the "Fases da Lua" by using `rounded-full` for avatars and `rounded-xl` (3rem) for content cards. The softness of these corners mimics the curve of the moon.

---

## 5. Components

### Buttons: The "Orbital" Style
- **Primary:** `primary` background, `on-primary` text. Shape: `rounded-full`. 
- **Secondary:** `surface-container-highest` background. No border.
- **Interaction:** On hover, the button should transition to `primary-container` with a subtle `2px` expansion (scale-up).

### Cards: The "Phases" Layout
- **Strict Rule:** No dividers. Use `spacing.4` to separate header from content.
- **Layout:** Images in cards should use `rounded-lg` (2rem). Overlap a circular "Status" badge (e.g., "Live") partially over the image edge to create a 3D layered effect.

### Input Fields: Soft Focus
- Background: `surface-container-low`.
- Border: `none` (use a `1px` bottom border of `outline-variant` only when focused).
- Shape: `rounded-md`.

### Lunar Navigation (Custom)
Instead of a standard bottom bar, use a semi-transparent floating dock.
- **Glassmorphism:** `surface` at 70% opacity + `backdrop-blur`.
- **Active State:** Instead of a simple color change, use a small `primary` colored dot (the "Moon") below the icon.

---

## 6. Do's and Don'ts

### Do:
- **Embrace Asymmetry:** Align text to the left but allow images to bleed off the right edge of a container to create a sense of movement.
- **Use "Lunar" Iconography:** Custom icons should use thin strokes (1.5px) and incorporate circular motifs (e.g., a "crescent moon" for a notification bell).
- **Prioritize Breathing Room:** If a screen feels "busy," increase the spacing between elements using the `spacing.12` or `spacing.16` tokens.

### Don't:
- **Don't use pure black:** Use `on-surface` (#1b1c1d) for text to maintain a high-end, softer feel.
- **Don't use "Card-in-Card" layout:** If you need to nest content, use a background color shift (e.g., `surface-container-lowest` inside `surface-container-low`), never double-borders.
- **Don't use standard icons:** Avoid generic Material or FontAwesome icons. Everything must feel custom, light, and "Lunar."
- **No Sharp Corners:** Avoid the `none` or `sm` roundedness scales unless for very specific technical constraints. The brand is "SexyLua"—it must feel soft and inviting.