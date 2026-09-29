This is a WordPress plugin that is served via composer + plugin-update-checker

## Development

- runs in wp-env

## Key Commands

Look into composer.json and package.json for the available commands

## Code style

- derive from the existing code
- keep comments as short as possible
- do not add comments if not really required
- also keep changesets as brief as possible
- if a changeset already exists that contains obsolete information, delete it

## Before making changes

- if the change is big, always first propose what you are planning, even if not in plan mode

## After making changes

- write tests where appropriate
- keep tests lean

## Commits and pull requests

- Keep PR descriptions as brief as possible. The title carries the meaning — most PRs
  here have an empty body or a few bullets at most. No headings, tables or walkthroughs.
- Never mention the agent, assistant or tool that wrote the code, in commit messages or
  PR descriptions. No `Co-Authored-By` trailers, no "Generated with ..." footers.

## Do not touch without explicit request

- No commits without explicit request
- do not edit TODO files

# Avoid conflicts with multiple sessions

- every new claude session creates a new branch off main if there are code changes
- when the task is complete, merge back into main without asking