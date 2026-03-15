import React from "react";

export default function FilterBar({ filters, updateFilter }) {
  return (
    <div className="flex flex-wrap gap-3 mb-6">

      {/* Sort */}
      <select
        value={filters.sort}
        onChange={(e) => updateFilter("sort", e.target.value)}
        className="rounded-full bg-white/5 border border-white/10 px-4 py-2 text-white"
      >
        <option value="relevance">Best Matches</option>
        <option value="price_asc">Price ↑</option>
        <option value="price_desc">Price ↓</option>
        <option value="name_asc">Name A-Z</option>
        <option value="name_desc">Name Z-A</option>
      </select>

      {/* Min Price */}
      <input
        type="number"
        placeholder="Min price"
        value={filters.priceMin}
        onChange={(e) => updateFilter("priceMin", e.target.value)}
        className="rounded-full bg-white/5 border border-white/10 px-4 py-2 text-white w-32"
      />

      {/* Max Price */}
      <input
        type="number"
        placeholder="Max price"
        value={filters.priceMax}
        onChange={(e) => updateFilter("priceMax", e.target.value)}
        className="rounded-full bg-white/5 border border-white/10 px-4 py-2 text-white w-32"
      />

      {/* In stock */}
      <label className="flex items-center gap-2 text-white">
        <input
          type="checkbox"
          checked={filters.inStock}
          onChange={(e) => updateFilter("inStock", e.target.checked)}
        />
        In stock
      </label>

    </div>
  );
}