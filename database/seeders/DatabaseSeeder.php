<?php

namespace Database\Seeders;

use App\Models\LibraryDoc;
use App\Services\Documents\DocumentIngestor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $documents = [
            [
                'title' => 'New teammate onboarding',
                'filename' => 'onboarding-manual.pdf',
                'body' => 'NORTHWIND DESK — NEW TEAMMATE ONBOARDING\n\nWelcome to Northwind Desk. On your first day, meet your manager, confirm your team and working hours, and review the support principles in this manual. Your manager requests access to the ticket workspace and team knowledge base. Use your company account and multi-factor authentication for all work tools.\n\nDuring your first week, shadow an experienced teammate for two support shifts. Read the refund, service-level, incident-severity, password-reset, and data-retention manuals before handling those requests independently. Ask your onboarding buddy when a process is unclear; never guess at a customer commitment.\n\nBy the end of week one, complete the practice queue, write a sample internal note, and review your answers with your manager. Keep customer information inside approved Northwind Desk tools. Do not copy customer data into personal notes, public chats, or unapproved services. Managers confirm readiness in a coaching session before granting independent queue access. The learning path is complete when all six practice cases have been reviewed and the teammate can locate the source manual for each policy question.',
            ],
            [
                'title' => 'Refunds and billing adjustments',
                'filename' => 'refunds-manual.pdf',
                'body' => 'NORTHWIND DESK — REFUNDS AND BILLING ADJUSTMENTS\n\nAgents may issue a full refund within 30 calendar days of the original purchase when the customer requests it and the transaction is settled. Verify the order identifier and payment status in the billing workspace first. Refunds return to the original payment method; allow 5 to 10 business days for the provider to post the credit. Never ask a customer to share a full card number.\n\nFor requests made more than 30 days after purchase, duplicate charges, partial refunds, or an order with a chargeback in progress, do not promise a refund. Add an internal note explaining the request and route the case to the Billing team for review. Billing approvals must be recorded on the ticket before the adjustment is made.\n\nIf the original transaction is still pending, explain that it cannot be refunded until it settles. If the customer cannot locate the credit after 10 business days, confirm the refund reference in the workspace and offer that reference so their payment provider can trace it. A refund does not automatically cancel a subscription; handle subscription cancellation as a separate request and confirm the effective date. Document every action and customer-facing confirmation in the ticket history.',
            ],
            [
                'title' => 'Support service levels',
                'filename' => 'sla-manual.pdf',
                'body' => 'NORTHWIND DESK — SUPPORT SERVICE LEVELS\n\nNorthwind Desk support is available Monday through Friday, 08:00–18:00 UTC, excluding published company holidays. The first-response target is one business hour for Critical cases, four business hours for High cases, and one business day for Normal cases. Low-priority requests receive a first response within three business days. These are response targets, not guaranteed resolution times.\n\nThe response clock starts when a complete request reaches the support queue during service hours. Requests received outside service hours enter the queue at the next opening. The clock pauses while the team is waiting for information from the requester. An automated acknowledgement does not count as a human first response.\n\nSet priority based on the documented impact, not on the wording or seniority of the requester. If a deadline is at risk, notify the shift lead before the target expires and leave a timestamped internal note. Keep the requester updated when investigation continues beyond the next expected update. Do not promise a resolution time unless the owning team has confirmed it.',
            ],
            [
                'title' => 'Account password reset',
                'filename' => 'password-reset-manual.pdf',
                'body' => 'NORTHWIND DESK — ACCOUNT PASSWORD RESET\n\nDirect customers to the official Northwind Desk sign-in page and ask them to select “Forgot password”. The reset email is sent to the verified email address on the account and expires after 30 minutes. Ask the customer to check spam and confirm they are using the same email address as their account. Never request or accept a current password, one-time code, or reset link.\n\nIf the message has not arrived after 10 minutes, verify the account email using the approved account lookup. Do not disclose whether an unverified email belongs to an account. For a verified address, agents may resend one reset email from the account workspace. Check the delivery status before sending another.\n\nIf the customer no longer has access to the verified email, route the request to Account Security for identity review. Do not change the email address or bypass multi-factor authentication in a support conversation. Record the case reference and tell the customer that Account Security will follow up through the verified contact channel. Escalate immediately if the account shows unfamiliar security changes.',
            ],
            [
                'title' => 'Incident severity and escalation',
                'filename' => 'incident-severity-manual.pdf',
                'body' => 'NORTHWIND DESK — INCIDENT SEVERITY AND ESCALATION\n\nSeverity 1 (Critical) means a confirmed widespread outage, active data exposure, or a core service unavailable to most customers with no workaround. Page the incident commander immediately, open the incident channel, and notify the on-call engineering lead. Keep updates factual and timestamped. Do not speculate about root cause or impact.\n\nSeverity 2 (High) means a major feature is degraded for multiple customers or a business-critical workflow is blocked, but the core service remains available or a workaround exists. Notify the shift lead immediately and create an engineering escalation with affected workflows, start time, and evidence.\n\nSeverity 3 (Normal) is a localized defect or individual customer issue with a reasonable workaround. Handle in the support queue and follow the normal response target. Severity 4 (Low) covers questions and cosmetic issues without material workflow impact.\n\nWhen impact is uncertain, start at the highest plausible severity, gather evidence, and ask the incident commander to confirm or adjust it. Never downgrade a severity without recording the reason. For every incident, post an update at least every 30 minutes until the commander announces resolution. Preserve logs using the approved incident process and avoid including secrets or unnecessary personal data in the channel.',
            ],
            [
                'title' => 'Data retention and deletion',
                'filename' => 'data-retention-manual.pdf',
                'body' => 'NORTHWIND DESK — DATA RETENTION AND DELETION\n\nSupport tickets and their internal notes are retained for 24 months after the last activity, then deleted from the active support workspace according to the scheduled retention job. Billing records follow the finance retention schedule and must not be deleted by support agents. Security incident records are retained for 36 months after closure with access limited to authorized responders.\n\nCustomers may request a copy or deletion of their account data through the privacy request form. Verify the request using the approved identity process, then route it to the Privacy team. Support agents must not promise a deletion date or remove records directly. The Privacy team checks legal holds, billing obligations, and open security investigations before confirming the scope and timeline.\n\nDo not retain customer data in personal notes, local downloads, or screenshots. If data is attached to the wrong ticket, restrict access where possible and notify the Privacy team. Record only the minimum information needed to resolve the request. A deletion request does not itself close an active support case or cancel a paid service.',
            ],
        ];

        foreach ($documents as $document) {
            $document['body'] = str_replace('\\n', "\n", $document['body']);
            LibraryDoc::query()->updateOrCreate(
                ['filename' => $document['filename']],
                ['title' => $document['title'], 'body' => $document['body']],
            );
        }

        app(DocumentIngestor::class)->ingestAll();
    }
}
