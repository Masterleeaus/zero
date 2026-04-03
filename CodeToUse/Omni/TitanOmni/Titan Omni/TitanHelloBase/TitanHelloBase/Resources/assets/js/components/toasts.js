export function toast(message) {
  if (!message) return;
  try { console.log('[TitanHello toast]', message); } catch (e) {}
}
