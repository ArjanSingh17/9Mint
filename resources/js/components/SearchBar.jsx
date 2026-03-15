// resources/js/components/SearchBar.jsx
import React from "react";

export default function SearchBar({
  term,
  setTerm,
  onSubmit,
  placeholder = "Search...",
  FiltersComponent = null,
  filters,
  updateFilter,
  onReset,
  showReset = true,
  className = "",
}) {
  return (
    <form
      onSubmit={(e) => {
        e.preventDefault();
        onSubmit();
      }}
      className={`flex items-center gap-2 flex-wrap ${className}`}
    >
      <div className="flex-1 min-w-[200px]">
        <input
          value={term}
          onChange={(e) => setTerm(e.target.value)}
          placeholder={placeholder}
          className="w-full rounded-full bg-white/5 border border-white/10 px-4 py-2 text-sm text-white placeholder-white/40 outline-none focus:border-white/25"
        />
      </div>

      {FiltersComponent ? (
        <FiltersComponent filters={filters} updateFilter={updateFilter} />
      ) : null}

      <button
        type="submit"
        className="rounded-full px-4 py-2 text-sm bg-white/10 border border-white/10 text-white hover:bg-white/15"
      >
        Search
      </button>

      {showReset ? (
        <button
          type="button"
          onClick={onReset}
          className="rounded-full px-4 py-2 text-sm bg-transparent border border-white/10 text-white/80 hover:text-white hover:bg-white/5"
        >
          Reset
        </button>
      ) : null}
    </form>
  );
}