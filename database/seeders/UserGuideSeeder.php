<?php

namespace Database\Seeders;

use App\Models\UserGuide;
use Illuminate\Database\Seeder;

class UserGuideSeeder extends Seeder
{
    public function run(): void
    {
        $steps = [
            [
                'title' => 'Sign in and open Travel Orders',
                'category' => 'Getting Started',
                'sort_order' => 1,
                'content' => <<<'HTML'
<p><strong>Goal:</strong> Open the DICT Region 2 Travel Order system and start a new request.</p>
<ol>
<li><strong>Step 1.1</strong> — Go to the Travel Order login page and sign in with your <code>@dict.gov.ph</code> account.</li>
<li><strong>Step 1.2</strong> — From the Dashboard, click <strong>Create Travel Order</strong>, or open <strong>Travel Orders</strong> in the sidebar and click <strong>Create Travel Order</strong>.</li>
<li><strong>Step 1.3</strong> — You are now on the create form and ready to enter travel details.</li>
</ol>
HTML,
            ],
            [
                'title' => 'Fill out the travel order form',
                'category' => 'Travel Orders',
                'sort_order' => 2,
                'content' => <<<'HTML'
<p><strong>Goal:</strong> Enter complete and accurate travel information before review.</p>
<ol>
<li><strong>Step 2.1</strong> — Set the overall <strong>travel period</strong> (start and end dates).</li>
<li><strong>Step 2.2</strong> — Add the <strong>itinerary</strong> (origin, destination, and segment dates).</li>
<li><strong>Step 2.3</strong> — Choose <strong>funding source</strong>, expenses, and <strong>vehicle</strong> if needed.</li>
<li><strong>Step 2.4</strong> — Add all <strong>travelers</strong> who will travel (not only yourself).</li>
<li><strong>Step 2.5</strong> — Select the correct <strong>workflow / approvers</strong>.</li>
<li><strong>Step 2.6</strong> — Attach supporting files if required, then write the <strong>purpose</strong> and remarks.</li>
<li><strong>Step 2.7</strong> — Click <strong>Save &amp; review</strong> to save the draft and open the document preview.</li>
</ol>
HTML,
            ],
            [
                'title' => 'Review the draft document',
                'category' => 'Travel Orders',
                'sort_order' => 3,
                'content' => <<<'HTML'
<p><strong>Goal:</strong> Check the draft Travel Order layout before it is sent for signatures.</p>
<ol>
<li><strong>Step 3.1</strong> — Read the draft preview carefully (travelers, destinations, dates, purpose, vehicle, funding).</li>
<li><strong>Step 3.2</strong> — If anything is wrong, click <strong>Edit</strong>, correct the form, and save again to return to the preview.</li>
<li><strong>Step 3.3</strong> — If you are not ready to submit, click <strong>Back to list</strong>. The request stays as a draft.</li>
<li><strong>Step 3.4</strong> — When the preview looks correct, continue to the next step to submit.</li>
</ol>
HTML,
            ],
            [
                'title' => 'Submit for approval',
                'category' => 'Travel Orders',
                'sort_order' => 4,
                'content' => <<<'HTML'
<p><strong>Goal:</strong> Send the travel order into the approval workflow.</p>
<ol>
<li><strong>Step 4.1</strong> — On the draft preview, click <strong>Submit for approval</strong>.</li>
<li><strong>Step 4.2</strong> — Confirm the submit dialog.</li>
<li><strong>Step 4.3</strong> — The first approver is notified by email and in-app notification.</li>
<li><strong>Step 4.4</strong> — Other listed travelers who have User accounts also receive an informational email that they were included.</li>
<li><strong>Step 4.5</strong> — Those travelers will receive another email only when the travel order is <strong>fully completed</strong> (with the official PDF), not on each intermediate approval.</li>
</ol>
HTML,
            ],
            [
                'title' => 'Track approvals, revisions, and completion',
                'category' => 'Approvals',
                'sort_order' => 5,
                'content' => <<<'HTML'
<p><strong>Goal:</strong> Understand what happens while approvers act on the request.</p>
<ol>
<li><strong>Step 5.1</strong> — Approvers open <strong>Travel Approvals</strong> and review the pending request.</li>
<li><strong>Step 5.2</strong> — <strong>Approve</strong> moves the request to the next signatory (or completes it on the last step).</li>
<li><strong>Step 5.3</strong> — <strong>For revision</strong> returns the request to you. Edit the draft, save, review, then <strong>Resubmit for approval</strong>.</li>
<li><strong>Step 5.4</strong> — <strong>Reject</strong> stops the workflow and records a reason.</li>
<li><strong>Step 5.5</strong> — You (the requestor) receive progress emails as the request moves forward.</li>
<li><strong>Step 5.6</strong> — On final approval, the official Travel Order number is assigned and the completed PDF is generated.</li>
</ol>
HTML,
            ],
            [
                'title' => 'Download and use the completed Travel Order',
                'category' => 'Tips',
                'sort_order' => 6,
                'content' => <<<'HTML'
<p><strong>Goal:</strong> Get the official document after full approval.</p>
<ol>
<li><strong>Step 6.1</strong> — Open the completed travel order from <strong>Travel Orders</strong>.</li>
<li><strong>Step 6.2</strong> — Click <strong>Download PDF</strong> (also sent by email to the requestor and listed travelers with User accounts).</li>
<li><strong>Step 6.3</strong> — Print or keep the PDF for travel records and liquidation requirements.</li>
<li><strong>Step 6.4</strong> — Reminder: submit your travel report within 7 days after the trip, as stated on the Travel Order.</li>
</ol>
HTML,
            ],
        ];

        // Replace starter guide content with the numbered step flow.
        UserGuide::query()->delete();

        foreach ($steps as $step) {
            UserGuide::create(array_merge($step, ['is_published' => true]));
        }
    }
}
