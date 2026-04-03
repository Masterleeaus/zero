/*******************************************************
                Accordion Sidebar Menu Start
*******************************************************/
var accItem = document.getElementsByClassName('accordionItem');
var accHD = document.getElementsByClassName('accordionItemHeading');
for (i = 0; i < accHD.length; i++) {
    accHD[i].addEventListener('click', toggleItem, false);
}

function toggleItem() {
    var itemClass = this.parentNode.className;

    for (i = 0; i < accItem.length; i++) {
        accItem[i].className = 'accordionItem closeIt';
    }
    if (itemClass == 'accordionItem closeIt') {
        this.parentNode.className = 'accordionItem openIt';
    }
}
/*******************************************************
                Accordion Sidebar Menu End
*******************************************************/

/*******************************************************
            Toggle The Side Navigation Start
*******************************************************/
// document.getElementById("sidebarToggle").addEventListener("click", toggleSidebar);

var ts = document.getElementById('sidebarToggle');
if (ts) {
    ts.addEventListener("click", toggleSidebar);
}

function toggleSidebar() {
    let toggle = document.querySelector('body');
    toggle.classList.toggle('sidebar-toggled');
}

window.addEventListener("resize", resiz);
function resiz() {
    if (screen.width < 769) {
        var element = document.querySelector("body");
        element.classList.remove("sidebar-toggled");
    }
}
/*******************************************************
            Toggle The Side Navigation End
*******************************************************/

/*******************************************************
               Header More Filter Start
*******************************************************/
function openMoreFilter() {
    var omf = document.getElementById("more_filter");
    omf.classList.add("in");
}

function closeMoreFilter() {
    var cls = document.getElementById("more_filter");
    cls.classList.remove("in");
}

if ($('#more_filter').length > 0) {
    $(document).on('mouseup', function(e)
    {
        var container = $("#more_filter");
        var searchField = $(".bs-searchbox");
        var select2Field = $("#bs-select-2");
        var selectField = $(".bs-container");

        // if the target of the click isn't the container nor a descendant of the container
        if (!container.is(e.target) && container.has(e.target).length === 0 && !searchField.is(e.target) && searchField.has(e.target).length === 0 && !select2Field.is(e.target) && select2Field.has(e.target).length === 0 && selectField.has(e.target).length === 0)
        {
            closeMoreFilter()
        }
    });
}


/*******************************************************
                Header More Filter End
*******************************************************/

/*******************************************************
                    Mobile Menu Start
*******************************************************/
function openMobileMenu() {
    var omm = document.getElementById("mobile_menu_collapse");
    omm.classList.add("toggled");

    var omm1 = document.getElementById("mobile_close_panel");
    omm1.classList.add("toggled");
}

function closeMobileMenu() {
    var cmm = document.getElementById("mobile_menu_collapse");
    cmm.classList.remove("toggled");

    var cmm1 = document.getElementById("mobile_close_panel");
    cmm1.classList.remove("toggled");
}
/*******************************************************
                    Mobile Menu End
*******************************************************/

/*******************************************************
              Mobile Admin Dashboard Open
*******************************************************/
function openAdminDashboard() {
    var oad1 = document.getElementById("mob-admin-dash");
    oad1.classList.add("in");

    var oad2 = document.getElementById("close-admin-overlay");
    oad2.classList.add("in");
}

var el = document.getElementById('close-admin-overlay');
if (el) {
    el.addEventListener("click", closeAdminDashboard);
}

var el = document.getElementById('close-admin');
if (el) {
    el.addEventListener("click", closeAdminDashboard);
}

function closeAdminDashboard() {
    var cad1 = document.getElementById("mob-admin-dash");
    cad1.classList.remove("in");

    var cad2 = document.getElementById("close-admin-overlay");
    cad2.classList.remove("in");
}
/*******************************************************
                    Mobile Settings End
*******************************************************/

/*******************************************************
                    Mobile Settings Open
*******************************************************/
function openSettingsSidebar() {
    var oss1 = document.getElementById("mob-settings-sidebar");
    oss1.classList.add("in");

    var oss2 = document.getElementById("close-settings-overlay");
    oss2.classList.add("in");
}

var el = document.getElementById('close-settings');
if (el) {
    el.addEventListener("click", closeSettingsSidebar);
}

var el = document.getElementById('close-settings-overlay');
if (el) {
    el.addEventListener("click", closeSettingsSidebar);
}

function closeSettingsSidebar() {
    var cls1 = document.getElementById("mob-settings-sidebar");
    cls1.classList.remove("in");

    var cls2 = document.getElementById("close-settings-overlay");
    cls2.classList.remove("in");
}
/*******************************************************
                    Mobile Settings End
*******************************************************/

/*******************************************************
                    Mobile Issue / Support Open
*******************************************************/
function openTicketsSidebar() {
    var ots1 = document.getElementById("issue / support-detail-contact");
    ots1.classList.add("in");

    var oss2 = document.getElementById("close-issues / support-overlay");
    oss2.classList.add("in");
}

var el = document.getElementById('close-issues / support');
if (el) {
    el.addEventListener("click", closeTicketsSidebar);
}

var el = document.getElementById('close-issues / support-overlay');
if (el) {
    el.addEventListener("click", closeTicketsSidebar);
}

function closeTicketsSidebar(){
    var cts1 = document.getElementById("issue / support-detail-contact");
    cts1.classList.remove("in");

    var cts2 = document.getElementById("close-issues / support-overlay");
    cts2.classList.remove("in");
}
/*******************************************************
                    Mobile Issue / Support End
*******************************************************/

/*******************************************************
                    Customer Detail Open
*******************************************************/
function openClientDetailSidebar() {
    var ocds1 = document.getElementById("mob-customer-detail");
    ocds1.classList.add("in");

    var ocds2 = document.getElementById("close-customer-overlay");
    ocds2.classList.add("in");

    // var ocds4 = document.getElementById("close-customer-detail");
    // ocds4.classList.remove("d-none");

    var ocds3 = document.getElementById("hide-site-menues");
    ocds3.classList.add("in");
}

var el = document.getElementById('close-customer-overlay');
if (el) {
    el.addEventListener("click", closeClientDetail);
}

var el = document.getElementById('close-customer-detail');
if (el) {
    el.addEventListener("click", closeClientDetail);
}

function closeClientDetail() {
    // var ocds4 = document.getElementById("close-customer-detail");
    // ocds4.classList.add("d-none");

    var ccd1 = document.getElementById("mob-customer-detail");
    ccd1.classList.remove("in");

    var ccd2 = document.getElementById("close-customer-overlay");
    ccd2.classList.remove("in");

    var ccd3 = document.getElementById("hide-site-menues");
    ccd3.classList.remove("in");
}
/*******************************************************
                    Customer Detail End
*******************************************************/

/*******************************************************
                    Site Menu Open
*******************************************************/
function openProjectSidebar() {
    var ops1 = document.getElementById("mob-site-menu");
    ops1.classList.add("in");

    var ops2 = document.getElementById("close-site-overlay");
    ops2.classList.add("in");
}

var el = document.getElementById('close-site-overlay');
if (el) {
    el.addEventListener("click", closeProjectSidebar);
}

var el = document.getElementById('close-sites');
if (el) {
    el.addEventListener("click", closeProjectSidebar);
}

function closeProjectSidebar() {
    var cps1 = document.getElementById("mob-site-menu");
    cps1.classList.remove("in");

    var cps2 = document.getElementById("close-site-overlay");
    cps2.classList.remove("in");
}
/*******************************************************
                    Site Menu End
*******************************************************/

/*******************************************************
                   Team Chat Item Tabs Start
*******************************************************/
function msgTabs(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";

    document.getElementById('msgContentRight').className += ' d-block';
}

function closeMessageTab() {
    var cmt = document.getElementById("msgContentRight");
    cmt.classList.remove("d-block");
}
/*******************************************************
                   Team Chat Item Tabs End
*******************************************************/

/*******************************************************
                   RTL Start
*******************************************************/
function rtl() {
    var rtl = document.querySelector("body");
    rtl.classList.toggle("rtl");
}
/*******************************************************
                   RTL End
*******************************************************/

/*******************************************************
                 Service Job Detail Start
*******************************************************/
function openTaskDetail() {
    var otd1 = document.getElementById("service job-detail-1");
    otd1.classList.add("in");

    var ops2 = document.getElementById("close-service job-detail-overlay");
    ops2.classList.add("in");

    var otd4 = document.getElementById("close-service job-detail");
    otd4.classList.add("in");
}

var el = document.getElementById('close-service job-detail-overlay');
if (el) {
    el.addEventListener("click", closeTaskDetail);
}

var el = document.getElementById('close-service job-detail');
if (el) {
    el.addEventListener("click", closeTaskDetail);
}

function closeTaskDetail() {
    var ctd1 = document.getElementById("service job-detail-1");
    ctd1.classList.remove("in");

    var ctd2 = document.getElementById("close-service job-detail-overlay");
    ctd2.classList.remove("in");

	sessionStorage.setItem('RIGHT_MODAL', 'opened');

    window.history.back();

    var ctd3 = document.getElementById("close-service job-detail");
    ctd3.classList.remove("in");
}
/*******************************************************
                 Service Job Detail End
*******************************************************/




// $(document).ready(function () {

//     //Datatables
//     $('#example').DataTable({
//         "language": {
//             "paginate": {
//                 "next": '<i class="icon-arrow-right icons"></i>',
//                 "previous": '<i class="icon-arrow-left icons"></i>'
//             }
//         },
//         "paging": true,
//         "ordering": false,
//         "info": false
//     });

// })


