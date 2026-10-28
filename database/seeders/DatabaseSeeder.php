<?php

namespace Database\Seeders;

use App\Models\Agreement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $agreements = [
            [
                'title' => 'Harbor Desk — Master Services Agreement',
                'body' => <<<'AGREEMENT'
1. Services and statements of work

Harbor Desk will provide the professional services described in one or more statements of work signed by both parties. Each statement of work will identify the deliverables, target dates, fees, and any acceptance criteria for that engagement. If a statement of work conflicts with this agreement, this agreement controls unless the statement expressly identifies the provision it replaces.

2. Fees and payment

The customer will pay the fees stated in each statement of work within thirty days after receiving an accurate invoice. Undisputed late amounts may accrue interest at the lower of one percent per month or the maximum rate allowed by law. The customer must notify Harbor Desk of a disputed invoice in reasonable detail and pay any undisputed portion when due.

3. Confidentiality

Each party may receive non-public business, technical, or financial information from the other party. The receiving party will use that information only to perform or receive services under this agreement, protect it using reasonable safeguards, and disclose it only to personnel who need to know and are bound by confidentiality obligations. These duties do not apply to information that becomes public without breach, was already known without restriction, is independently developed, or is rightfully received from another source.

4. Intellectual property

Upon full payment of the applicable fees, Harbor Desk assigns to the customer its rights in deliverables specifically created for the customer under a statement of work. Harbor Desk retains all rights in its pre-existing materials, tools, templates, know-how, and general methods. To the extent those materials are included in a deliverable, Harbor Desk grants the customer a perpetual, worldwide, non-exclusive license to use them as part of that deliverable for the customer's internal business purposes.

5. Warranties and disclaimer

Harbor Desk warrants that it will perform the services in a professional and workmanlike manner. The customer must give written notice of a warranty issue within thirty days after delivery, and Harbor Desk will re-perform the affected services as the customer's exclusive remedy for breach of this warranty. Except for the express warranty in this section, services and deliverables are provided without other warranties, whether express, implied, or statutory.

6. Limitation of liability

To the maximum extent permitted by law, neither party will be liable to the other for any indirect, incidental, special, consequential, exemplary, or punitive damages, or for lost profits, revenue, data, or business opportunities, even if advised that such damages were possible. Each party's total aggregate liability arising out of or related to this agreement will not exceed the fees paid or payable under the applicable statement of work during the twelve months before the event giving rise to the claim. The limits in this section do not apply to a party's fraud, willful misconduct, payment obligations, or misuse of the other party's intellectual property or confidential information.

7. Term and termination

This agreement begins on the effective date and continues until terminated. Either party may terminate this agreement or an individual statement of work for convenience on thirty days' written notice. Either party may terminate for a material breach if the other party fails to cure that breach within thirty days after receiving written notice. On termination, the customer will pay for services performed and approved expenses incurred through the effective termination date.

8. General terms

The parties are independent contractors. Neither party may assign this agreement without the other party's written consent, except to a successor in connection with a merger or sale of substantially all of its assets. This agreement is governed by the laws of the State of Oregon, without regard to conflict-of-law rules. This agreement and its signed statements of work are the entire agreement between the parties concerning its subject matter.
AGREEMENT,
            ],
            [
                'title' => 'Harbor Desk — Mutual Non-Disclosure Agreement',
                'body' => <<<'AGREEMENT'
1. Purpose

The parties wish to evaluate a potential business relationship and may share confidential information for that purpose. This agreement governs how each party handles information disclosed by or on behalf of the other party in connection with those discussions.

2. Confidential information

Confidential information means non-public information disclosed in any form that is identified as confidential or that a reasonable person would understand to be confidential given its nature and the circumstances of disclosure. It may include product plans, designs, pricing, customer research, security materials, and business forecasts. Information is not confidential if the receiving party can show it was publicly available without breach, already known without a duty of confidence, independently developed, or received lawfully from a third party.

3. Permitted use and protection

The receiving party will use confidential information only to evaluate the potential business relationship and will protect it with at least the same care it uses for its own similar information, and no less than reasonable care. The receiving party may share information with its employees and professional advisers who need it for the permitted purpose and are subject to confidentiality duties. The receiving party remains responsible for their compliance with this agreement.

4. Required disclosure

If a receiving party is legally required to disclose confidential information, it will, where legally permitted, give prompt notice to the disclosing party and reasonably cooperate with efforts to seek confidential treatment. The receiving party may disclose only the portion required by law.

5. Term and return of information

This agreement starts on the date of the last signature and applies to disclosures made during the following two years. The confidentiality and restricted-use obligations continue for three years after each disclosure. Upon written request or the end of discussions, the receiving party will promptly return or destroy the disclosing party's confidential information, except for copies retained in routine backups or required by law, which remain protected under this agreement.

6. No license or commitment

All confidential information remains the property of the disclosing party. No license or other intellectual property right is granted by disclosure. Neither party is required to proceed with a transaction, purchase a service, or disclose any particular information.

7. Remedies and liability

Each party acknowledges that unauthorized disclosure may cause harm that is difficult to remedy with money alone, and a party may seek appropriate equitable relief in addition to remedies available at law. Nothing in this agreement creates an uncapped obligation for indirect or consequential damages.

8. General terms

This agreement is governed by the laws of the State of Oregon. It is the complete agreement about its subject matter and may be changed only by a writing signed by both parties. If one provision is unenforceable, the remaining provisions continue in effect.
AGREEMENT,
            ],
            [
                'title' => 'Harbor Desk — Service Level Agreement',
                'body' => <<<'AGREEMENT'
1. Service availability

Harbor Desk will use commercially reasonable efforts to make the hosted workspace available at least 99.9 percent of each calendar month, measured on a 24-hour basis. Availability excludes scheduled maintenance, emergency maintenance, events outside Harbor Desk's reasonable control, customer systems or networks, and outages caused by the customer's configuration or misuse.

2. Service credits

If monthly availability falls below the target, the customer may request a service credit equal to five percent of the monthly subscription fee for each full percentage point below the target, up to a maximum credit of twenty-five percent of that month's subscription fee. The customer must submit a written request with supporting details within thirty days after the month in question. Approved credits apply to a future invoice and are not cash refunds.

3. Support response

The customer may submit support requests through the designated support channel. Harbor Desk will acknowledge critical service interruptions within one business hour, high-priority issues within four business hours, and standard questions within one business day. Response targets measure acknowledgment, not resolution, and do not guarantee a particular fix time.

4. Maintenance and notice

Harbor Desk may perform scheduled maintenance during a published maintenance window, normally outside standard business hours. Harbor Desk will use reasonable efforts to give at least forty-eight hours' advance notice of planned maintenance expected to materially affect availability. Emergency maintenance may occur without advance notice when needed to protect the service or customer data.

5. Customer responsibilities

The customer will maintain compatible devices and network access, keep account credentials secure, and promptly notify Harbor Desk of suspected unauthorized access. The customer is responsible for its users' activity and for maintaining copies of any content it submits to the workspace.

6. Service changes and term

This service level applies while the customer has an active subscription to the hosted workspace. Harbor Desk may update operational details from time to time, but will not materially reduce the availability target during a paid subscription term without notice. Either party may terminate the associated subscription as permitted by its order form.

7. Exclusive service remedy

Service credits described in this agreement are the customer's sole and exclusive remedy for failure to meet the monthly availability target. Credits are subject to the limits and request process in this agreement and do not apply to fees disputed in good faith.

8. Definitions

Available means that an authorized user can successfully authenticate and load the primary workspace interface. Monthly availability is calculated by subtracting qualifying unavailable minutes from total minutes in the calendar month, dividing the result by total minutes, and multiplying by one hundred.
AGREEMENT,
            ],
        ];

        foreach ($agreements as $agreement) {
            Agreement::query()->updateOrCreate(
                ['title' => $agreement['title']],
                ['body' => $agreement['body']],
            );
        }
    }
}
