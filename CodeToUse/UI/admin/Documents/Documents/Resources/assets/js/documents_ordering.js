// Drag-drop ordering helper (minimal; integrate into your UI where needed)
window.DocumentsOrdering = {
  post: async function(url, ids, csrf) {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ ids })
    });
    return res.json();
  }
};
