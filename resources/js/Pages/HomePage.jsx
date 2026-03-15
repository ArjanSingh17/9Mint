import React from "react";
import useSearchFilters from "../hooks/useSearchFilters";

export default function HomePage({ collections = [] }) {
  const search = useSearchFilters({
    initialItems: collections,
    fields: [
      "name",
      "description",
      "items.name",   // if your collection has items: [{name}]
      "nfts.name",    // if your collection has nfts: [{name}]
      "products.name" // if your collection has products: [{name}]
    ],
  });

  return (
    <div className="p-6">
      {/* IMPORTANT: render search.items */}
      {search.items.map((c) => (
        <div key={c.id} className="mb-6 p-6 rounded-lg border">
          <h3 className="font-bold">{c.name}</h3>
          <p className="opacity-80">{c.description}</p>

          {c.stock != null && (
            <p className="text-sm opacity-70">Stock: {c.stock}</p>
          )}
        </div>
      ))}
    </div>
  );
}