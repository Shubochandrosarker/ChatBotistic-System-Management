"use client";

import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from "react";

/**
 * Theme system — light is the default, dark via an explicit toggle.
 * Persisted in localStorage; applied via `data-theme="light"|"dark"` on
 * `<html>` (globals.css keys every dark: variant off that attribute).
 *
 * No next-themes dependency so the app stays deployable as a plain
 * standalone build. `themeInitScript` is inlined in the root layout's
 * `<head>` and runs before paint so there is no flash of the wrong
 * theme; this provider keeps React state in sync afterwards.
 */

export type Theme = "light" | "dark";

const STORAGE_KEY = "chatbotistic-theme";

interface ThemeContextValue {
  theme: Theme;
  setTheme: (theme: Theme) => void;
  toggleTheme: () => void;
}

const ThemeContext = createContext<ThemeContextValue | null>(null);

function applyTheme(theme: Theme) {
  const root = document.documentElement;
  root.setAttribute("data-theme", theme);
  root.style.colorScheme = theme;
}

export function ThemeProvider({ children }: { children: React.ReactNode }) {
  // Light is the default until the mount effect below reconciles with
  // whatever the blocking init script already applied.
  const [theme, setThemeState] = useState<Theme>("light");

  useEffect(() => {
    const stored = window.localStorage.getItem(STORAGE_KEY);
    const initial: Theme = stored === "dark" ? "dark" : "light";
    // eslint-disable-next-line react-hooks/set-state-in-effect
    setThemeState(initial);
  }, []);

  const setTheme = useCallback((next: Theme) => {
    window.localStorage.setItem(STORAGE_KEY, next);
    applyTheme(next);
    setThemeState(next);
  }, []);

  const toggleTheme = useCallback(() => {
    setThemeState((prev) => {
      const next: Theme = prev === "dark" ? "light" : "dark";
      window.localStorage.setItem(STORAGE_KEY, next);
      applyTheme(next);
      return next;
    });
  }, []);

  const value = useMemo(
    () => ({ theme, setTheme, toggleTheme }),
    [theme, setTheme, toggleTheme]
  );

  return (
    <ThemeContext.Provider value={value}>{children}</ThemeContext.Provider>
  );
}

export function useTheme(): ThemeContextValue {
  const ctx = useContext(ThemeContext);
  if (!ctx) throw new Error("useTheme must be used within ThemeProvider");
  return ctx;
}

/**
 * Inline, blocking script — runs in the root layout's <head> before
 * paint. Light is the default; dark only applies when explicitly
 * stored (no system-preference auto-switch, per the design spec).
 */
export const themeInitScript = `
(function(){try{
var t=localStorage.getItem('${STORAGE_KEY}');
var d=t==='dark';
var r=document.documentElement;
r.setAttribute('data-theme', d?'dark':'light');
r.style.colorScheme=d?'dark':'light';
}catch(e){}})();
`;
