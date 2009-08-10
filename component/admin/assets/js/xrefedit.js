/**
 * @version 1.0 $Id: settings.js 30 2009-05-08 10:22:21Z roland $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
 
window.addEvent('domready', function() {
  SqueezeBox.initialize({handler: 'iframe', size: {x: 600, y: 500}});

      $$('a.xrefmodal').each(function(el) {
        el.addEvent('click', function(e) {
          new Event(e).stop();
          SqueezeBox.fromElement(el);
        });
      });
      
  $('sbox-btn-close').addEvent('click', function() {
    //alert('closing');
  });
  
  // add delete link for xrefs
  $$('tr.xref-details').each(addremove);
});

/**
 * adds a remove button for xref at the end of the line
 */
function addremove(item)
{
  var cell = item.getElement('td.cell-delxref');
  new Element('a', {'href': '#'}).addEvent('click', removexref.bind(item)).appendText(textremove).injectInside(cell);
}

function updatexref(id, venue, date, time, published)
{
  if ($('xref-'+id)) {
    var tr = $('xref-'+id);
    var newtr = buildxreftr(id, venue, date, time, published);
    tr.replaceWith(newtr);
  }
  else {
    var newtr = buildxreftr(id, venue, date, time, published);
    newtr.injectBefore($('add-xref'));
  }
  addremove(newtr);
  $('sbox-window').close();
}

function removexref(event)
{
  if (confirm(confirmremove)) 
  {
    var url = 'index.php?option=com_redevent&controller=events&task=removexref&tmpl=component&format=raw';
    var myXhr = new XHR(
                    {
                    method: 'post',
                    onRequest: reqsent.bind(this),
                    onFailure: reqfailed.bind(this),
                    onSuccess: xrefdeleted.bind(this)
                    }
        );
    var querystring = 'xref=' + this.id.substr(5)
                        ;
    myXhr.send(url, querystring); 
  }
}

function buildxreftr(id, venue, date, time, published)
{
  var tr = new Element('tr', {'id': 'xref-'+id, 'class': 'xref-details'});
  var tdlink = new Element('td').injectInside(tr);
  var link = new Element('a', {'href': 'index.php?option=com_redevent&controller=events&task=editxref&tmpl=component&xref='+id})
                 .appendText(edittext).injectInside(tdlink).addEvent('click', function(e) {
          new Event(e).stop();
          SqueezeBox.fromElement(this);
        });;
  new Element('td').appendText(venue).injectInside(tr);
  new Element('td').appendText(date).injectInside(tr);
  new Element('td').appendText(time).injectInside(tr);
  if (published == 1) {
    new Element('td').appendText(textyes).injectInside(tr);
  }
  else {
    new Element('td').appendText(textno).injectInside(tr);
  }  
  new Element('td', {'class': 'cell-delxref'}).injectInside(tr);
  return tr;
}

  function xrefdeleted(response)
  {
    var resp = response.split(":");
    if (resp[0] != '0') {
      this.remove();
    }
    else {
      alert(response.substr(resp[0].length+1));
    }
  }

  function reqsent()
  {
    this.addClass('ajax-loading');
  }
  
  function reqfailed()
  {
    this.removeClass('ajax-loading');
  }