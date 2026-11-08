export type Dictionary = { [key: string]: string | Dictionary }

function lookup(dict: Dictionary, keys: string[]): string | undefined {
  let current: string | Dictionary | undefined = dict
  for (const key of keys) {
    if (current && typeof current === 'object' && key in current) {
      current = current[key]
    } else {
      return undefined
    }
  }
  return typeof current === 'string' ? current : undefined
}

/**
 * Resolve a dotted key against a dictionary, falling back to a secondary
 * dictionary and finally to the key itself. Replaces `{param}` placeholders.
 */
export function translate(
  dict: Dictionary,
  fallbackDict: Dictionary,
  path: string,
  params: Record<string, string | number> = {},
): string {
  const keys = path.split('.')
  const template = lookup(dict, keys) ?? lookup(fallbackDict, keys)
  if (template === undefined) return path

  return template.replace(/\{(\w+)\}/g, (_, key: string) =>
    params[key] !== undefined ? String(params[key]) : `{${key}}`,
  )
}

/** Flatten a dictionary into dotted leaf keys (used to verify locale parity). */
export function flattenKeys(dict: Dictionary, prefix = ''): string[] {
  return Object.entries(dict).flatMap(([key, value]) =>
    typeof value === 'string'
      ? [`${prefix}${key}`]
      : flattenKeys(value, `${prefix}${key}.`),
  )
}
