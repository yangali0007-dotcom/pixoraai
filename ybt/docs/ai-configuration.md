# AI configuration

AI is disabled by default. The Owner configures a provider and explicitly enables external access in Settings → AI & integrations.

## Implemented adapters

- OpenAI-compatible chat completions: OpenAI, OpenRouter or a compatible custom provider. Use the full HTTPS chat-completions endpoint.
- Anthropic Messages: full HTTPS Messages endpoint; model ID is sent in the body.
- Google Gemini generateContent: full HTTPS endpoint including the chosen model path; the API key is sent in a header.

Provider/model compatibility must be checked through **Test default provider**. Models with different parameter/response contracts may require an adapter update. API calls have a 10-second connection timeout, 40-second request timeout, 2 MB response bound and 1,200-token output request. Current OpenAI-compatible requests use `max_completion_tokens`; unsupported provider/model combinations will fail safely. No live provider has been verified in the supplied preview without an Owner-configured API account.

Keys are authenticated-encrypted using libsodium and a separate application key. They are never returned to the browser, placed in prompts/logs, or included in ordinary operational backups. Replacing the default adds a new encrypted configuration; remove all credentials to revoke local access and rotate keys with the provider too.

Only public HTTPS endpoints on port 443 are allowed. DNS targets are checked and pinned for each connection. Redirects and proxies are disabled. Internal endpoints, embedded credentials and query-string API keys are rejected.

## Available read tools

Select Business summary, Outstanding invoices, Customer search or Upcoming tasks. The server checks AI access, the Owner’s module policy and underlying user permissions. Financial context requires `ai.financial` and relevant financial module permissions. Read results are bounded. The provider receives only the selected context, rather than direct SQL access or database credentials. Returned text is escaped.

There is a 20-request per-user/business hourly limit. Usage records count requests, not measured token usage or estimated costs. The provider controls costs, retention and training policies; review its current terms. Do not assume identical policies across providers.

## Changes and approvals

Task creation is the initial proposal type. A user with `ai.propose` and `tasks.create` can prepare an exact title/date/description. The Owner must approve the proposal separately. Tokens are random, hashed, expire after 30 minutes and cannot be replayed. Requester access is rechecked at execution. The provider cannot call the approval operation.

Automatic natural-language mutation extraction, other proposal types, streamed responses, provider spending controls, AI chart rendering, long-term memory and conversation summaries remain incomplete. Conversation text is currently used as a display history, not automatically resent as context. See acceptance.md.
