import {
  Backpack,
  Bike,
  BookOpen,
  Box,
  Brush,
  Gamepad2,
  Home,
  Laptop,
  Music,
  ShoppingBag,
  Tag,
} from "lucide-react";
import type { CategoryIconKey } from "@/lib/types/admin";

export function CategoryIcon({
  icon,
  className = "h-5 w-5",
}: {
  icon: CategoryIconKey | string;
  className?: string;
}) {
  switch (icon) {
    case "bi-book":
      return <BookOpen className={className} aria-hidden="true" />;
    case "bi-laptop":
      return <Laptop className={className} aria-hidden="true" />;
    case "bi-bicycle":
      return <Bike className={className} aria-hidden="true" />;
    case "bi-backpack":
      return <Backpack className={className} aria-hidden="true" />;
    case "bi-house":
      return <Home className={className} aria-hidden="true" />;
    case "bi-music-note":
      return <Music className={className} aria-hidden="true" />;
    case "bi-brush":
      return <Brush className={className} aria-hidden="true" />;
    case "bi-controller":
      return <Gamepad2 className={className} aria-hidden="true" />;
    case "bi-bag":
      return <ShoppingBag className={className} aria-hidden="true" />;
    case "bi-box-seam":
      return <Box className={className} aria-hidden="true" />;
    default:
      return <Tag className={className} aria-hidden="true" />;
  }
}
