// resources/js/Pages/CollectionShow.jsx
import React, { useEffect, useMemo, useRef, useState } from "react";
import SearchBar from "@/components/SearchBar";
import FilterPills from "@/components/FilterPills";
import useSearchFilters from "@/hooks/useSearchFilters";
import { searchNftsFromDb, applyNftFilters } from "@/services/searchApi";

export default function CollectionShow({ collection, nfts = [] }) {
  const [baseList, setBaseList] = useState(nfts);
  const [visible, setVisible] = useState(nfts);
  const [scrollToId, setScrollToId] = useState(null);

  // Map<nftId, HTMLElement>
  const itemRefs = useRef(new Map());

  const filterOptions = useMemo(
    () => ({
      sort: ["relevance", "price_asc", "price_desc", "name_asc", "name_desc"],
      inStock: ["In Stock Only"],
      // priceMin / priceMax are numeric inputs (so we won't show dropdown options for them)
      priceMin: [],
      priceMax: [],
    }),
    []
  );

  const { term, setTerm, filters, updateFilter, submit, reset } = useSearchFilters({
    defaultFilters: {
      sort: "relevance",
      priceMin: "",
      priceMax: "",
      inStock: false,
    },
    // NOTE: if you truly don't want URL params, remove filterParams completely
    // and comment out the URL sync inside the hook (see note at bottom)
    filterParams: ["sort", "priceMin", "priceMax", "inStock"],
    onSubmit: async ({ term, filters }) => {
      const clean = (term || "").trim();

      // If no search term, show original list (still apply filters)
      if (!clean) {
        setBaseList(nfts);
        setVisible(applyNftFilters(nfts, filters));
        return;
      }

      // Search within THIS collection only
      const data = await searchNftsFromDb({
        term: clean,
        collection: collection?.name,
      });

      const merged = [...(data?.matches || []), ...(data?.others || [])];

      setBaseList(merged);
      setVisible(applyNftFilters(merged, filters));

      // focus param wins if present; otherwise scroll to first match
      const sp = new URLSearchParams(window.location.search);
      const focus = sp.get("focus");
      if (focus) setScrollToId(Number(focus));
      else if (data?.matches?.[0]?.id) setScrollToId(data.matches[0].id);
    },
  });

  // Run once on load
  useEffect(() => {
    submit();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // Re-apply filters when filters change
  useEffect(() => {
    setVisible(applyNftFilters(baseList, filters));
  }, [filters, baseList]);

  // Scroll to focused item
  useEffect(() => {
    if (!scrollToId) return;
    const el = itemRefs.current.get(scrollToId);
    if (el) el.scrollIntoView({ behavior: "smooth", block: "start" });
    setScrollToId(null);
  }, [scrollToId]);

  return (
    <div className="max-w-6xl mx-auto px-4 py-6">
      <h2 className="text-white text-2xl font-semibold mb-4">{collection?.name}</h2>

      {/* Search */}
      <SearchBar
        term={term}
        setTerm={setTerm}
        onSubmit={submit}
        onReset={reset}
        placeholder={`Search inside ${collection?.name || "collection"}...`}
        showReset={true}
      />

      {/* ✅ Filters directly under title/search, above grid */}
      <div className="mt-4">
        <FilterPills
          filters={filters}
          updateFilter={(key, val) => {
            // special handling for inStock pill
            if (key === "inStock") {
              updateFilter("inStock", !filters.inStock);
              return;
            }
            updateFilter(key, val);
          }}
          options={filterOptions}
        />

        {/* Numeric price inputs */}
        <div className="mt-3 flex flex-wrap gap-3">
          <input
            value={filters.priceMin || ""}
            onChange={(e) => updateFilter("priceMin", e.target.value)}
            placeholder="Min price"
            className="w-32 rounded-full bg-white/5 border border-white/10 px-4 py-2 text-sm text-white placeholder-white/40 outline-none focus:border-white/25"
          />
          <input
            value={filters.priceMax || ""}
            onChange={(e) => updateFilter("priceMax", e.target.value)}
            placeholder="Max price"
            className="w-32 rounded-full bg-white/5 border border-white/10 px-4 py-2 text-sm text-white placeholder-white/40 outline-none focus:border-white/25"
          />

          <button
            type="button"
            onClick={() => {
              updateFilter("sort", "relevance");
              updateFilter("priceMin", "");
              updateFilter("priceMax", "");
              updateFilter("inStock", false);
            }}
            className="rounded-full px-4 py-2 text-sm bg-transparent border border-white/10 text-white/80 hover:text-white hover:bg-white/5"
          >
            Clear
          </button>
        </div>
      </div>

      {/* Grid */}
      <div className="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
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
          </div>
        ))}
      </div>

      {visible.length === 0 && (
        <div className="text-center text-white mt-6">No results found</div>
      )}
    </div>
  );
}