export function toast(message) {
  if (!message) return;
  try { console.log('[TitanTalk toast]', message); } catch (e) {}
}
