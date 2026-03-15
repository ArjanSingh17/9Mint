import React from "react";
import { createRoot } from "react-dom/client";
import CollectionShow from "@/Pages/CollectionShow";

const el = document.getElementById("collection-show-root");

if (el) {
  const props = JSON.parse(el.dataset.props || "{}");
  createRoot(el).render(<CollectionShow {...props} />);
}