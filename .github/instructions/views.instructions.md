---
applyTo: "resources/views/**/*.blade.php,app/**/Views/**/*.php"
---

# View Instructions

- Respect the host theme/view system.
- Prefer adapting existing themed views over creating parallel disconnected UI trees.
- Keep UI changes minimal during merge passes unless the task explicitly requests redesign.
- Do not break route/view bindings.
- If moving views from source extensions, align them to the host view path conventions and update controller references carefully.
- Preserve existing CSS class systems and layout patterns where possible.
