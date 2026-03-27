<?php
class ActionsObjectBanner
{
    public function formObjectOptions($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $langs, $db;

        if (in_array('propalcard', explode(':', $parameters['context'])) ||
            in_array('ordercard', explode(':', $parameters['context'])) ||
            in_array('invoicecard', explode(':', $parameters['context'])) ||
            in_array('expeditioncard', explode(':', $parameters['context']))) {

            $langs->load("objectbanner@objectbanner");

            // Fetch linked objects
            $object->fetchObjectLinked();
            $linkedObjects = $object->linkedObjects;

            // We need to find the "chain": Propal -> Order -> Invoice/Expedition
            $propals = array();
            $orders = array();
            $invoices = array();
            $expeditions = array();

            // Identify current object type and add it
            if ($object->element == 'propal') $propals[$object->id] = $object;
            elseif ($object->element == 'commande') $orders[$object->id] = $object;
            elseif ($object->element == 'facture') $invoices[$object->id] = $object;
            elseif ($object->element == 'expedition') $expeditions[$object->id] = $object;

            // Helper to classify objects
            $classify = function($obj) use (&$propals, &$orders, &$invoices, &$expeditions) {
                if ($obj->element == 'propal') $propals[$obj->id] = $obj;
                elseif ($obj->element == 'commande') $orders[$obj->id] = $obj;
                elseif ($obj->element == 'facture') $invoices[$obj->id] = $obj;
                elseif ($obj->element == 'expedition') $expeditions[$obj->id] = $obj;
            };

            // Check linked objects of current object
            if (!empty($linkedObjects)) {
                foreach ($linkedObjects as $type => $objects) {
                    foreach ($objects as $linkedObj) {
                        $classify($linkedObj);
                    }
                }
            }

            // Deep linking logic (1 level hop)
            // If we have Orders, check their parents/children
            if (!empty($orders)) {
                foreach ($orders as $order) {
                    // Avoid re-fetching if it's the current object (already fetched)
                    if ($order->id == $object->id && !empty($linkedObjects)) continue;

                    $order->fetchObjectLinked();
                    if (!empty($order->linkedObjects)) {
                        foreach ($order->linkedObjects as $type => $objects) {
                            foreach ($objects as $linkedObj) {
                                $classify($linkedObj);
                            }
                        }
                    }
                }
            }

            // If we have Invoices but no Order (rare direct link Propal->Invoice?), check Invoice links
            if (!empty($invoices) && empty($orders)) {
                 foreach ($invoices as $invoice) {
                    if ($invoice->id == $object->id && !empty($linkedObjects)) continue;

                    $invoice->fetchObjectLinked();
                    if (!empty($invoice->linkedObjects)) {
                        foreach ($invoice->linkedObjects as $type => $objects) {
                            foreach ($objects as $linkedObj) {
                                $classify($linkedObj);
                            }
                        }
                    }
                 }
            }

            // If we have Expeditions but no Order, check Expedition links
            if (!empty($expeditions) && empty($orders)) {
                 foreach ($expeditions as $expedition) {
                    if ($expedition->id == $object->id && !empty($linkedObjects)) continue;

                    $expedition->fetchObjectLinked();
                    if (!empty($expedition->linkedObjects)) {
                        foreach ($expedition->linkedObjects as $type => $objects) {
                            foreach ($objects as $linkedObj) {
                                $classify($linkedObj);
                            }
                        }
                    }
                 }
            }

            // Now render the banner
            $this->printBanner($propals, $orders, $invoices, $expeditions, $object);
        }

        return 0;
    }

    private function printBanner($propals, $orders, $invoices, $expeditions, $currentObject)
    {
        global $langs, $conf;

        // Get toggle settings (default to 1 if not set)
        $showPropal = getDolGlobalInt('OBJECTBANNER_SHOW_PROPAL', 1);
        $showCommande = getDolGlobalInt('OBJECTBANNER_SHOW_COMMANDE', 1);
        $showFacture = getDolGlobalInt('OBJECTBANNER_SHOW_FACTURE', 1);
        $showExpedition = getDolGlobalInt('OBJECTBANNER_SHOW_EXPEDITION', 1);

        // Maximum items to show before collapsing to count
        $maxItems = 3;

        // Only show if at least one other object exists (besides current)
        $total_count = count($propals) + count($orders) + count($invoices) + count($expeditions);
        if ($total_count < 2) return;

        // Count visible sections
        $visibleSections = array();
        if ($showPropal) $visibleSections[] = 'propal';
        if ($showCommande) $visibleSections[] = 'commande';
        if ($showFacture) $visibleSections[] = 'facture';
        if ($showExpedition) $visibleSections[] = 'expedition';

        // Don't show banner if no sections are enabled
        if (empty($visibleSections)) return;

        print '<div id="objectbanner-container" class="objectbanner-container">';

        // Helper function to render a section
        $renderSection = function($title, $objects, $currentObj, $elementType) use ($langs, $maxItems) {
            $sectionType = '';
            if ($elementType == 'propal') $sectionType = 'propal';
            if ($elementType == 'commande') $sectionType = 'commande';
            if ($elementType == 'facture') $sectionType = 'facture';
            if ($elementType == 'expedition') $sectionType = 'expedition';

            $isActive = ($currentObj->element == $sectionType);
            $count = count($objects);

            print '<div class="objectbanner-item ' . ($isActive ? 'active' : '') . '">';
            print '<div class="objectbanner-content">';
            print '<div class="objectbanner-title">' . $title . '</div>';

            if (empty($objects)) {
                print '<div class="objectbanner-empty">-</div>';
            } elseif ($count > $maxItems) {
                // Show count with link to scroll to linked objects table
                print '<div class="objectbanner-items-container">';
                print '<a href="#" class="objectbanner-count-link" data-element="' . $elementType . '" onclick="objectbannerScrollToLinked(\'' . $elementType . '\'); return false;">';
                print '<span class="objectbanner-count badge badge-info">' . $count . ' ' . $title . ($count > 1 ? 's' : '') . '</span>';
                print '</a>';
                print '</div>';
            } else {
                print '<div class="objectbanner-items-container">';
                foreach ($objects as $obj) {
                    print '<div class="objectbanner-ref-row">';
                    print '<span class="objectbanner-ref">' . $obj->getNomUrl(1) . '</span>';
                    print '<span class="objectbanner-sep"> | </span>';
                    print '<span class="objectbanner-status">' . $obj->getLibStatut(1) . '</span>';
                    print '</div>';
                }
                print '</div>';
            }
            print '</div>';
            print '</div>';
        };

        $sectionsRendered = 0;

        // Propal Section
        if ($showPropal) {
            $renderSection($langs->trans("Proposal"), $propals, $currentObject, 'propal');
            $sectionsRendered++;
        }

        // Arrow between Propal and Order
        if ($showPropal && ($showCommande || $showFacture || $showExpedition)) {
            print '<div class="objectbanner-arrow"><i class="fa fa-arrow-right"></i></div>';
        }

        // Order Section
        if ($showCommande) {
            $renderSection($langs->trans("Order"), $orders, $currentObject, 'commande');
            $sectionsRendered++;
        }

        // Arrow between Order and Invoice/Expedition
        if ($showCommande && ($showFacture || $showExpedition)) {
            print '<div class="objectbanner-arrow"><i class="fa fa-arrow-right"></i></div>';
        }

        // Invoice Section
        if ($showFacture) {
            $renderSection($langs->trans("Invoice"), $invoices, $currentObject, 'facture');
            $sectionsRendered++;
        }

        // Arrow between Invoice and Expedition
        if ($showFacture && $showExpedition) {
            print '<div class="objectbanner-arrow"><i class="fa fa-arrow-right"></i></div>';
        }

        // Expedition Section
        if ($showExpedition) {
            $renderSection($langs->trans("Shipment"), $expeditions, $currentObject, 'expedition');
            $sectionsRendered++;
        }

        print '</div>'; // End container

        // Move banner to top of #id-right and add scroll function
        print '<script type="text/javascript">
            // Smooth scroll function for linked objects
            function objectbannerScrollToLinked(elementType) {
                // Map element types to table data-element values
                var tableElement = elementType;
                if (elementType == "commande") tableElement = "commande";
                if (elementType == "facture") tableElement = "facture";
                if (elementType == "propal") tableElement = "propal";
                if (elementType == "expedition") tableElement = "expedition";

                // Find the linked objects table - try different selectors
                var table = document.querySelector(\'table[data-block="showLinkedObject"]\');

                // If not found, try finding the "Linked objects" section
                if (!table) {
                    // Look for the div containing linked objects
                    var linkedDiv = document.querySelector(".div-table-responsive-no-min");
                    if (linkedDiv) {
                        table = linkedDiv;
                    }
                }

                // Also try to find by looking for "Objets liés" or "Linked objects" title
                if (!table) {
                    var allTables = document.querySelectorAll("table.noborder");
                    for (var i = 0; i < allTables.length; i++) {
                        var t = allTables[i];
                        if (t.getAttribute("data-block") === "showLinkedObject") {
                            table = t;
                            break;
                        }
                    }
                }

                if (table) {
                    // Get banner height for offset
                    var banner = document.getElementById("objectbanner-container");
                    var bannerHeight = banner ? banner.offsetHeight : 0;

                    // Get header height
                    var headerHeight = 0;
                    var topHeader = document.getElementById("id-top");
                    if (topHeader) {
                        headerHeight = topHeader.offsetHeight;
                    } else {
                        var sideNav = document.querySelector(".side-nav-horiz");
                        if (sideNav) headerHeight = sideNav.offsetHeight;
                    }

                    // Calculate scroll position with offset for sticky elements
                    var tableRect = table.getBoundingClientRect();
                    var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    var targetPosition = tableRect.top + scrollTop - headerHeight - bannerHeight - 20;

                    // Smooth scroll
                    window.scrollTo({
                        top: targetPosition,
                        behavior: "smooth"
                    });

                    // Highlight effect
                    table.style.transition = "box-shadow 0.3s ease";
                    table.style.boxShadow = "0 0 10px rgba(0, 123, 255, 0.5)";
                    setTimeout(function() {
                        table.style.boxShadow = "";
                    }, 2000);
                }
            }

            (function() {
                var banner = document.getElementById("objectbanner-container");
                var idRight = document.getElementById("id-right");

                if (idRight && banner) {
                    idRight.prepend(banner);
                    idRight.style.paddingTop = "0";

                    // Calculate header height for sticky positioning
                    var headerHeight = 0;
                    var topHeader = document.getElementById("id-top");
                    if (topHeader) {
                        headerHeight = topHeader.offsetHeight;
                    } else {
                         var sideNav = document.querySelector(".side-nav-horiz");
                         if (sideNav) headerHeight = sideNav.offsetHeight;
                    }

                    // Apply top offset so it sticks BELOW the header
                    banner.style.top = headerHeight + "px";
                } else {
                    // Fallback
                    var fiche = document.querySelector(".fiche");
                    if (fiche && banner) fiche.prepend(banner);
                }
            })();
        </script>';
    }
}
