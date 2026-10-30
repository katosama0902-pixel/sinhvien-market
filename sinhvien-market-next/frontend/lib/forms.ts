import type { FieldValues, Path, UseFormSetError } from "react-hook-form";

export function applyApiFieldErrors<T extends FieldValues>(
  fields: Record<string, string> | undefined,
  setError: UseFormSetError<T>,
  allowed: readonly string[],
): boolean {
  if (!fields) return false;
  let first = true;
  let applied = false;
  for (const [name, message] of Object.entries(fields)) {
    if (!allowed.includes(name)) continue;
    setError(name as Path<T>, { type: "server", message }, { shouldFocus: first });
    first = false;
    applied = true;
  }
  return applied;
}
