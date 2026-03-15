// resources/js/Pages/Products.jsx
import React, { useEffect, useRef, useState } from "react";
import SearchBar from "@/components/SearchBar";
import FilterBar from "@/components/FilterBar";
import useSearchFilters from "@/hooks/useSearchFilters";
import { searchNftsFromDb, applyNftFilters } from "@/services/searchApi";

export default function Products({ products = [] }) {
  const [baseList, setBaseList] = useState(products);
  const [visible, setVisible] = useState(products);
  const [scrollToId, setScrollToId] = useState(null);
  const itemRefs = useRef(new Map());

  const { term, setTerm, filters, updateFilter, submit, reset } =
    useSearchFilters({
      syncToUrl: false, // ✅ no url
      defaultFilters: {
        sort: "relevance",
        priceMin: "",
        priceMax: "",
        inStock: false,
      },
      onSubmit: async ({ term, filters }) => {
        const data = await searchNftsFromDb({ term });
        const merged = [...(data.matches || []), ...(data.others || [])];

        setBaseList(merged);
        setVisible(applyNftFilters(merged, filters));

        if (data.matches?.[0]?.id) setScrollToId(data.matches[0].id);
      },
    });

  useEffect(() => {
    submit();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    setVisible(applyNftFilters(baseList, filters));
  }, [filters, baseList]);

  useEffect(() => {
    if (!scrollToId) return;
    const el = itemRefs.current.get(scrollToId);
    if (el) el.scrollIntoView({ behavior: "smooth", block: "start" });
    setScrollToId(null);
  }, [scrollToId]);

  return (
    <div className="max-w-6xl mx-auto px-4 py-6">
      <h2 className="text-white text-2xl font-semibold mb-4">Products</h2>

      <SearchBar
        term={term}
        setTerm={setTerm}
        onSubmit={submit}
        onReset={reset}
        placeholder="Search products..."
        showReset={true}
      />

      {/* ✅ filter bar shows under title/search */}
      <FilterBar filters={filters} updateFilter={updateFilter} />

      <div className="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        {visible.map((p) => (
          <div
            key={p.id}
            ref={(el) => {
              if (el) itemRefs.current.set(p.id, el);
              else itemRefs.current.delete(p.id);
            }}
            className="rounded-2xl bg-black/30 border border-white/10 p-4"
          >
            <div className="text-white font-semibold">{p.name}</div>
            <div className="text-white/60 text-sm mt-1">{p.collection_name}</div>
            <div className="text-white/60 text-sm mt-1">
              £{Number(p.primary_ref_amount ?? p.price ?? 0).toFixed(2)}
            </div>
          </div>
        ))}
      </div>

      {visible.length === 0 && (
        <div className="text-center text-white mt-6">No results found</div>
      )}
    </div>
  );
}