@extends('layouts.layout')
@section('title', 'Change Logs')

@section('content')
<div class="col col-md-10 offset-1">

<div class="alert alert-warning text-center">
If you have any issues, please contact us, <a href="http://support.medcol.mw/">ICT Support Helpdesk System</a>.
</div>

<h2>v1.2.6</h2>
Released on 16th March, 2020. <br>
<br>
<h4>New Features</h4>
<ul>
<li><strong>Archivable</strong>: Old procurement requisitions which corresponding manually can be archived. (Administrator only) <br> Archived requisitions are only listed the "Archive" menu.</li>
<li>Now you can search period of created and updated columns.</li>
</ul>

<h2>v1.2.5</h2>
Released on 11th March, 2020. <br>
<br>
<h4>New Features</h4>
<ul>
<li><strong>Justify Modify</strong>: Procurement Specialist and Procurement Officers can amend procurement requisitions.  </li>
<li>New tab named "change log" on the procurement requisition page is available. It shows the history what PS and PO modified.</li>
<li>Added "owner" column on the delegation page</li>
</ul>

<h2>v1.2.4</h2>
Released on 2nd March, 2020. <br>
<br>
<h4>New Features</h4>
<ul>
<li>Available to use US dollar or any other currency for procurement requisitions </li>
</ul>
<h4>For Administrator</h4>
<ul>
<li>Send system alert mails if there is not assigned the assistant account for the specify unit</li>
</ul>

<h2>v1.2.3</h2>
Released on 28th February, 2020. <br>
<br>
<h4>Issues</h4>
<ul>
<li>Fixed delegating back bug on Procurement requisition</li>
<li>Added "BOX" and "AMPULES" on uom</li>
</ul>
<h4>For Administrator</h4>
<ul>
<li>Easy to be identify testing / live server</li>
</ul>

<h2>v1.2.2</h2>
Released on 26th February, 2020. <br>
<br>
<h4>Issues</h4>
<ul>
<li>Prices can be set decimals (Tambala) now.</li>
</ul>

<h2>v1.2.1</h2>
Released on 25th February, 2020. <br>
<br>
<h4>New Features</h4>
<ul>
<li>Added delegation status buttons to search on delegations page.</li>
<li>Now you can see all requisitions you are delegated by someone OR YOU DELEGATED TO SOMEONE on the delegations page.</li>
</ul>

<h2>v1.2.0</h2>
Released on 24th February, 2020. <br>
<br>
<h4>New Features</h4>
<ul>
<li>Now you can send messages to anyone on the requisition trail</li>
</ul>
<h4>For Administrators</h4>
<ul>
<li>For administrator, added roles-users settings</li>
</ul>

<h2>v1.1.1</h2>
Released on 12th February, 2020. <br>
<br>
<h4>Issues</h4>
<ul>
<li>Fixed issue about deleting userUnit if user has 2 or more roles</li>
<li>Added old url pattern redirect (for old login page)</li>
</ul>
<h4>For Administrators</h4>
<ul>
<li>Added permission descriptions</li>
</ul>


<h2>v1.1.0</h2>
Released on 10th February, 2020. <br>
<br>
<h4>New Features</h4>
<ul>
<li>For delegated user, now you are able to send it back anytime even if you haven't finished actions.</li>
<li>For administrator, added permission settings</li>
</ul>

<h2>v1.0.0</h2>
Released on 7th February, 2020. <br>
<br>
We have launched a new version of R-Plus. <br>
This has been developed using a new technology which will make it easier to extend it with new functionality in future.
<br>
<h4>New Features</h4>
<ul>
<li><strong>Brand new requisition list and searching function.</strong> You never miss requisitions which you have to be confirmed.</li>
<li><strong>Rating supplier system.</strong>After getting your items, you can rate the supplier with your comment. This will help choosing nice suppliers in the future.</li>
<li><strong>Selectable Project Investigator.</strong></li>
</ul>


{{--
<h2>v0.1.20191112</h2>
Released on 12th November, 2019.
<h4>Issues</h4>
<ul>
<li>Fixed setting the wrong company DB when adding new unit</li>
</ul>
<h4>Features</h4>
<ul>
<li>Display the assigned accountant of each units</li>
<li>Able to select the assigned accountant when adding / editing unit</li>
</ul>
--}}

</div>
@endsection

