// resources/js/Pages/Collection.jsx
import React, { useEffect, useState } from "react";
import SearchBar from "@/components/SearchBar";
import FilterPills from "@/components/FilterPills";
import useSearchFilters from "@/hooks/useSearchFilters";

export default function Collection({ collections = [] }) {
  const [baseList, setBaseList] = useState(collections);
  const [visible, setVisible] = useState(collections);

  const {
    term,
    setTerm,
    filters,
    updateFilter,
    submit,
    reset,
  } = useSearchFilters({
    defaultFilters: {
      category: "",
      inStock: false,
    },
    filterParams: ["name", "category", "inStock"],
    onSubmit: ({ term, filters }) => {
      const q = term.trim().toLowerCase();

      const filteredByName = baseList.filter((c) =>
        c.name.toLowerCase().includes(q)
      );

      setBaseList(filteredByName);

      // Apply additional filters
      let finalList = [...filteredByName];

      if (filters.category) {
        finalList = finalList.filter(
          (c) => c.category === filters.category
        );
      }

      if (filters.inStock) {
        finalList = finalList.filter((c) => c.stock > 0);
      }

      setVisible(finalList);
    },
  });

  // run once on load
  useEffect(() => submit(), []);

  return (
    <div className="max-w-6xl mx-auto px-4 py-6">
      <h2 className="text-white text-2xl font-semibold mb-4">
        Our Collections
      </h2>

      {/* Search + Filter UI */}
      <SearchBar
        term={term}
        setTerm={setTerm}
        onSubmit={submit}
        onReset={() => {
          reset();
          setVisible(baseList);
        }}
        placeholder="Search collections..."
        showReset={true}
      />

      {/* New Filter pills */}
      <div className="mt-4">
        <FilterPills
          filters={filters}
          updateFilter={updateFilter}
          options={[
            {
              key: "category",
              label: "Category",
              type: "select",
              values: [
                { label: "All", value: "" },
                { label: "Art", value: "Art" },
                { label: "Fashion", value: "Fashion" },
                { label: "Collectible", value: "Collectible" },
              ],
            },
            {
              key: "inStock",
              label: "In Stock",
              type: "checkbox",
            },
          ]}
        />
      </div>

      {/* Collections List */}
      <div className="mt-8 space-y-6">
        {visible.map((c) => (
          <div
            key={c.id}
            className="rounded-2xl bg-black/30 border border-white/10 p-4"
          >
            <a
              href={`/collections/${c.slug}`}
              className="text-lg text-white underline"
            >
              {c.name}
            </a>
            {c.description && (
              <div className="text-white/60 text-sm mt-1">
                {c.description}
              </div>
            )}
          </div>
        ))}
      </div>

      {visible.length === 0 && (
        <div className="text-center text-white mt-6">
          No collections found
        </div>
      )}
    </div>
  );
}