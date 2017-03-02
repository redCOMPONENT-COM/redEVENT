<?php
/**
 * @package     Aesir.Plugin
 * @subpackage  Aesir_Field.Redevent_venue
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

extract($displayData);
?>
{% for session in sessions %}

{% set startdate = session.dates %}
{% set enddate = session.enddates %}

<div class="row list-item {% if  loop.index % 2 == 0 %} blue {% endif %} ">

	<div class="hidden time-list">
		{% if session.session_date == "0000-00-00" %}
		Request date
		{% else %}
		{{ session.session_date|date("M_Y")  }}
		{% endif %}
	</div>
	<div class="hidden lang-list">{{ session.session_language }}</div>

	<div class="course">
		{{ session.getEvent.title }}
	</div>

	<div class="date">
		{% if session.dates == '0000-00-00' %}
		Open date
		{% else %}
		{{session.dates}}
		{% endif %}
	</div>

	<div class="duration hidden-sm hidden-xs">
		{% if session.durationdays > 0 %}
		{{ session.durationdays }}
		{% else %}
		-
		{% endif %}
	</div>

	<div class="lang hidden-sm hidden-xs">
		{{ session.session_language }}
	</div>

	<div class="location">
		<i class="flag-icon flag-icon-{{ session.venue.country|lower }}"></i> {{ session.venue.venue }}

	</div>

	<div class="price">
		{% if session.prices > '' %}
		{% for item in session.prices %}

		{{ item.currency }} {{ item.price }}

		{% endfor %}
		{% else %}
		-
		{% endif %}
	</div>

	<div class="seats hidden-sm hidden-xs">
		{% if session.maxattendees > 0 %}
		{{ session.left }}
		{% else %}
		-
		{% endif %}
	</div>

	<div class="book">
		<a href="{{ session.getReditemLink|raw }}"><i class="small-arrow" aria-hidden="true"><img src="images/ICONS/arrow_read_more.svg" /></i>Book course</a>
	</div>
</div>


{% endfor %}
