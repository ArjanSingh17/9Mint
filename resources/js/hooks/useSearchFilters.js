// resources/js/hooks/useSearchFilters.js
import { useMemo, useState } from "react";

export default function useSearchFilters({
  queryParam = "q",
  defaultFilters = {},
  filterParams = [],
  onSubmit,
  syncToUrl = false, // ✅ DEFAULT OFF (no URL)
} = {}) {
  const initial = useMemo(() => {
    if (!syncToUrl) {
      return { term: "", filters: { ...defaultFilters } };
    }

    const sp = new URLSearchParams(window.location.search);
    const term = sp.get(queryParam) || "";
    const filters = { ...defaultFilters };

    filterParams.forEach((k) => {
      const v = sp.get(k);
      if (v != null) filters[k] = v;
    });

    return { term, filters };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const [term, setTerm] = useState(initial.term);
  const [filters, setFilters] = useState(initial.filters);

  const updateFilter = (key, value) => {
    setFilters((prev) => ({ ...prev, [key]: value }));
  };

  const submit = async (e) => {
    if (e?.preventDefault) e.preventDefault();
    return onSubmit?.({ term, filters });
  };

  const reset = async () => {
    setTerm("");
    setFilters({ ...defaultFilters });
    return onSubmit?.({ term: "", filters: { ...defaultFilters } });
  };

  return {
    term,
    setTerm,
    filters,
    setFilters,
    updateFilter,
    submit,
    reset,
  };
}