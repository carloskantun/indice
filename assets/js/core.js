export async function fetchJson(url, options = {}) {
  const res = await fetch(url, options);
  if (!res.ok) throw new Error('Request failed');
  return res.json();
}

export async function fetchText(url, options = {}) {
  const res = await fetch(url, options);
  if (!res.ok) throw new Error('Request failed');
  return res.text();
}

export function showAlert(message, type = 'info', container = document.body) {
  const div = document.createElement('div');
  div.className = `alert alert-${type}`;
  div.textContent = message;
  container.appendChild(div);
  setTimeout(() => div.remove(), 5000);
}
