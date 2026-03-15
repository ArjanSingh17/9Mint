// resources/js/components/FilterBar.jsx
import React from "react";

export default function FilterBar({ filters, updateFilter }) {
  return (
    <div className="mt-4 flex gap-3 flex-wrap items-center">
      {/* Sort */}
      <select
        value={filters.sort || "relevance"}
        onChange={(e) => updateFilter("sort", e.target.value)}
        className="px-4 py-2 rounded-full bg-white/10 text-white border border-white/20 outline-none"
      >
        <option value="relevance">Sort: Relevance</option>
        <option value="price_asc">Price: Low → High</option>
        <option value="price_desc">Price: High → Low</option>
        <option value="name_asc">Name: A → Z</option>
        <option value="name_desc">Name: Z → A</option>
      </select>

      {/* Price Min */}
      <input
        type="number"
        value={filters.priceMin || ""}
        onChange={(e) => updateFilter("priceMin", e.target.value)}
        placeholder="Min £"
        className="w-28 px-4 py-2 rounded-full bg-white/10 text-white border border-white/20 outline-none placeholder-white/40"
      />

      {/* Price Max */}
      <input
        type="number"
        value={filters.priceMax || ""}
        onChange={(e) => updateFilter("priceMax", e.target.value)}
        placeholder="Max £"
        className="w-28 px-4 py-2 rounded-full bg-white/10 text-white border border-white/20 outline-none placeholder-white/40"
      />

      {/* In Stock */}
      <label className="px-4 py-2 rounded-full bg-white/10 text-white border border-white/20 flex items-center gap-2">
        <input
          type="checkbox"
          checked={!!filters.inStock}
          onChange={(e) => updateFilter("inStock", e.target.checked)}
        />
        In Stock
      </label>

      {/* Clear */}
      <button
        type="button"
        onClick={() => {
          updateFilter("sort", "relevance");
          updateFilter("priceMin", "");
          updateFilter("priceMax", "");
          updateFilter("inStock", false);
        }}
        className="px-4 py-2 rounded-full bg-white/10 text-white border border-white/20 hover:bg-white/15"
      >
        Clear
      </button>
    </div>
  );
}