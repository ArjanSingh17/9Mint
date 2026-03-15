// resources/js/services/searchApi.js

// 🔎 Search NFTs from backend
export async function searchNftsFromDb({ term = "", collection = "", signal } = {}) {
  const url = new URL("/api/v1/search/nfts", window.location.origin);

  if (term) url.searchParams.set("q", term);
  if (collection) url.searchParams.set("collection", collection);

  const res = await fetch(url.toString(), {
    method: "GET",
    headers: { Accept: "application/json" },
    signal,
  });

  if (!res.ok) {
    const text = await res.text().catch(() => "");
    throw new Error(`Search failed (${res.status}): ${text}`);
  }

  return res.json();
}


// ✅ Apply filters (used in Products.jsx and CollectionShow.jsx)
export function applyNftFilters(items = [], filters = {}) {
  const {
    sort = "relevance",   // relevance | price_asc | price_desc | name_asc | name_desc
    priceMin = "",
    priceMax = "",
    inStock = false,
  } = filters;

  let result = [...items];

  // Price helpers (supports multiple possible field names)
  const getPrice = (item) =>
    Number(item.primary_ref_amount ?? item.price ?? 0);

  const getStock = (item) =>
    Number(item.editions_remaining ?? item.editionsRemaining ?? 0);

  // Price filter
  if (priceMin !== "") {
    const min = Number(priceMin);
    if (!Number.isNaN(min)) {
      result = result.filter((item) => getPrice(item) >= min);
    }
  }

  if (priceMax !== "") {
    const max = Number(priceMax);
    if (!Number.isNaN(max)) {
      result = result.filter((item) => getPrice(item) <= max);
    }
  }

  // In-stock filter
  if (inStock) {
    result = result.filter((item) => getStock(item) > 0);
  }

  // Sorting
  switch (sort) {
    case "price_asc":
      result.sort((a, b) => getPrice(a) - getPrice(b));
      break;

    case "price_desc":
      result.sort((a, b) => getPrice(b) - getPrice(a));
      break;

    case "name_asc":
      result.sort((a, b) =>
        String(a.name ?? "").localeCompare(String(b.name ?? ""))
      );
      break;

    case "name_desc":
      result.sort((a, b) =>
        String(b.name ?? "").localeCompare(String(a.name ?? ""))
      );
      break;

    // relevance = keep original order
    default:
      break;
  }

  return result;
}