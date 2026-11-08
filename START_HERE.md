# Start Here — Novixa WorkOS

## 1. Prepare the repository

Create a private Git repository, for example `Novixa-dev/novixa-workos`. Copy this kit's contents into the repository root. Keep the `.claude` directory and its contents.

If this is an existing repository, do not overwrite project files blindly. Ask Claude to inspect the current structure and merge these instructions without deleting working code or configuration.

## 2. Open Claude Code

From the repository root, run:

```bash
claude
```

On Windows, use the supported Git Bash setup or WSL if required by your installed Claude Code version. Follow Anthropic's current setup guide: <https://docs.anthropic.com/en/docs/claude-code/getting-started>.

## 3. First instruction to Claude

Paste this message into Claude Code:

```text
Read CLAUDE.md, START_HERE.md, MASTER_PROMPT.md, and every document under docs/. Also inspect all existing repository files, Git status/history, installed toolchain, and deployment configuration before making changes.

Follow MASTER_PROMPT.md as the governing project brief. Start with Phase 0 discovery and produce the requested audit, assumptions, architecture decisions, risks, and an executable implementation plan. Do not begin feature implementation until the discovery artefacts are complete. Do not ask routine questions; make safe, reversible assumptions and record them. Ask me only before destructive actions, irreversible data changes, purchasing/paid services, exposing the app publicly, or decisions that materially affect security, privacy, or architecture and cannot be safely deferred.

After Phase 0, continue through the approved plan in small, testable vertical slices. Maintain docs/20-PROGRESS.md, docs/19-DECISIONS-AND-OPEN-QUESTIONS.md, and docs/21-TRACEABILITY.md as you work. Never claim a feature is complete unless it is implemented, tested, and its acceptance criteria pass. Do not use placeholder buttons or fake data in production paths.
```

## 4. Recommended working rhythm

- Start in Claude Code Plan Mode for the first discovery pass if available.
- Review the resulting `docs/IMPLEMENTATION_AUDIT.md` (Claude should create this) and plan before allowing major architecture changes.
- Implement one vertical slice at a time: database → API → authorization → UI → tests → documentation.
- Run the test suite after each slice and before each commit.
- Use Git commits with clear scopes. Never allow an agent to rewrite history or force-push without explicit approval.
- Restart Claude Code after creating `.claude/agents/` for the first time if agents are not detected.

## 5. Suggested initial Git setup

```bash
git init
git add .
git commit -m "docs: add Novixa WorkOS product and engineering brief"
```

Only run this if the repository is genuinely new and does not already have Git history.

## 6. The core rule

Build a reliable, useful internal system for Novixa's real workflows. Do not optimize for the number of modules, lines of code, animations, or novelty. Prefer a small feature that works end-to-end over a broad collection of incomplete screens.
