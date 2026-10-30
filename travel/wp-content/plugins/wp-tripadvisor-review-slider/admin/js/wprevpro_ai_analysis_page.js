(function($){
function aiEscapeHTML(s){ return (s==null)?'':String(s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c];}); }
	$(document).ready(function(){
		var aiCharts = { doughnut: null, timeline: null };
		var printRestore = null;
		var printRestoreTimer = null;

		(function loadLatest(){
			var data = { action: 'wptripadvisor_ai_get_latest_report', wpfb_nonce: adminjs_script_vars.wpfb_nonce };
			$.post(adminjs_script_vars.ajax_url, data).then(function(resp){
				if(resp && resp.success && resp.data){
					var r = resp.data;
					setOverviewText(r.report_markdown || '');
					setReportHeading(r.title, r.created_at);
					if(r.report_json){ $('#ai_report_json').val(JSON.stringify(r.report_json, null, 2)); renderAIReport(r.report_json); }
					setEmptyState(false);
				}
			});
		})();

		$('#ai_export_json').on('click', function(){
			var content = $('#ai_report_json').val() || '{}';
			var blob = new Blob([content], {type: 'application/json'});
			var url = URL.createObjectURL(blob);
			var a = document.createElement('a');
			a.href = url; a.download = 'ai-analysis.json'; a.click();
			URL.revokeObjectURL(url);
		});

		$('#ai_export_md').on('click', function(){
			var report = window._aiLastReport || {};
			var meta = window._aiReportMeta || {};
			var parts = [];
			if(meta.title){ parts.push('# ' + meta.title); }
			if(meta.created_at){ parts.push(formatReportDate(meta.created_at)); }
			if(report.summary){ parts.push('## Summary'); parts.push(report.summary); }
			if(report.metrics){
				var m = report.metrics;
				parts.push('## Metrics');
				parts.push('Total reviews: ' + ((m.counts && m.counts.total_reviews) || 0));
				parts.push('Average rating: ' + (m.avg_rating != null ? m.avg_rating : '—'));
			}
			if(Array.isArray(report.themes) && report.themes.length){
				parts.push('## Themes');
				report.themes.forEach(function(t){ parts.push('- ' + (t.name||'') + (t.count != null ? ' ('+t.count+')' : '')); });
			}
			if(Array.isArray(report.pain_points) && report.pain_points.length){
				parts.push('## Pain Points');
				report.pain_points.forEach(function(p){ parts.push('- [' + (p.severity||'') + '] ' + (p.issue||'')); });
			}
			if(Array.isArray(report.recommendations) && report.recommendations.length){
				parts.push('## Recommendations');
				report.recommendations.forEach(function(r){
					parts.push('- ' + (typeof r === 'string' ? r : (r.title || r.text || '')));
				});
			}
			if(report.review_growth && report.review_growth.summary){
				parts.push('## How to Grow Reviews');
				parts.push(report.review_growth.summary);
			}
			var readable = $('#ai_report_markdown').text().trim();
			if(readable){ parts.push('## Readable Report'); parts.push(readable); }
			var content = parts.join('\n\n');
			var blob = new Blob([content], {type: 'text/markdown'});
			var url = URL.createObjectURL(blob);
			var a = document.createElement('a');
			a.href = url; a.download = 'ai-analysis-full.md'; a.click();
			URL.revokeObjectURL(url);
		});

		$('#ai_toggle_json').on('click', function(){
			var $c = $('#ai_json_container');
			var isHidden = ($c.css('display') === 'none');
			$c.toggle(isHidden);
			$('#ai_toggle_json').text(isHidden ? 'Hide JSON' : 'Show Structured JSON');
		});

		function expandReportForPrint(){
			printRestore = {
				overviewCollapsed: $('#ai_report_markdown').hasClass('is-collapsed'),
				openFaqs: $('.ai-faq-item.is-open').map(function(){ return $('.ai-faq-item').index(this); }).get(),
				openGrowth: $('.ai-growth-item.is-open').map(function(){ return $('.ai-growth-item').index(this); }).get()
			};
			setOverviewExpanded(true, false);
			$('.ai-faq-item, .ai-growth-item').addClass('is-open');
			$('.ai-quote-more').show();
			$('.ai-readmore').hide();
			$('body').addClass('ai-printing');
			$('#wprevpro_ai_analysis_page').addClass('is-print-ready');
		}

		function restoreReportAfterPrint(){
			if(!printRestore){ return; }
			var state = printRestore;
			printRestore = null;
			if(printRestoreTimer){ clearTimeout(printRestoreTimer); printRestoreTimer = null; }
			$('body').removeClass('ai-printing');
			$('#wprevpro_ai_analysis_page').removeClass('is-print-ready');
			$('.ai-quote-more').hide();
			$('.ai-readmore').show().text('Read more');
			$('.ai-faq-item').removeClass('is-open').each(function(i){
				if(state.openFaqs.indexOf(i) !== -1){ $(this).addClass('is-open'); }
			});
			$('.ai-growth-item').removeClass('is-open').each(function(i){
				if(state.openGrowth.indexOf(i) !== -1){ $(this).addClass('is-open'); }
			});
			setOverviewExpanded(!state.overviewCollapsed, false);
		}

		$('#ai_print_report').on('click', function(){
			expandReportForPrint();
			window.setTimeout(function(){
				window.print();
				printRestoreTimer = window.setTimeout(restoreReportAfterPrint, 400);
			}, 50);
		});
		if(window.matchMedia){
			var printMq = window.matchMedia('print');
			if(printMq.addEventListener){
				printMq.addEventListener('change', function(e){ if(!e.matches){ restoreReportAfterPrint(); } });
			} else if(printMq.addListener){
				printMq.addListener(function(e){ if(!e.matches){ restoreReportAfterPrint(); } });
			}
		}
		$(window).on('afterprint', restoreReportAfterPrint);

		function destroyChart(key){
			if(aiCharts[key]){
				try { aiCharts[key].destroy(); } catch(e){}
				aiCharts[key] = null;
			}
		}

		function formatReportDate(createdAt){
			if(!createdAt){ return ''; }
			var d = new Date(String(createdAt).replace(' ', 'T'));
			if(!isNaN(d.getTime())){
				return d.toLocaleString(undefined, { year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: '2-digit' });
			}
			return String(createdAt);
		}

		function setReportHeading(title, createdAt){
			var heading = String(title || '').trim() || 'AI Analysis';
			$('#ai_report_heading_title').text(heading);
			$('#ai_report_heading_date').text(formatReportDate(createdAt));
			window._aiReportMeta = { title: heading, created_at: createdAt || '' };
		}

		function setOverviewExpanded(expanded, animate){
			var $body = $('#ai_report_markdown');
			var $btn = $('#ai_overview_toggle');
			var isCollapsed = $body.hasClass('is-collapsed');
			var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			if(!animate || reduceMotion){
				$body.off('transitionend.aiOverview');
				$body.css({ 'max-height': '', overflow: '' });
				$body.toggleClass('is-collapsed', !expanded);
				$btn.attr('aria-expanded', expanded ? 'true' : 'false').text(expanded ? 'Hide overview' : 'Read full overview');
				return;
			}
			if(expanded === !isCollapsed){
				$btn.attr('aria-expanded', expanded ? 'true' : 'false').text(expanded ? 'Hide overview' : 'Read full overview');
				return;
			}
			$btn.attr('aria-expanded', expanded ? 'true' : 'false').text(expanded ? 'Hide overview' : 'Read full overview');
			$body.off('transitionend.aiOverview');
			if(expanded){
				var start = $body.outerHeight();
				$body.removeClass('is-collapsed');
				var end = $body.prop('scrollHeight');
				$body.css({ overflow: 'hidden', 'max-height': start + 'px' });
				$body[0].offsetHeight;
				$body.css('max-height', end + 'px');
				$body.one('transitionend.aiOverview', function(){
					if(!$body.hasClass('is-collapsed')){
						$body.css({ 'max-height': '', overflow: '' });
					}
				});
			} else {
				var openHeight = $body.prop('scrollHeight');
				$body.css({ overflow: 'hidden', 'max-height': openHeight + 'px' });
				$body[0].offsetHeight;
				$body.addClass('is-collapsed');
				$body.css('max-height', '7.6em');
				$body.one('transitionend.aiOverview', function(){
					if($body.hasClass('is-collapsed')){
						$body.css({ 'max-height': '', overflow: '' });
					}
				});
			}
		}

		function setOverviewText(md){
			if (typeof md !== 'string') { try { md = JSON.stringify(md); } catch(e) { md = String(md); } }
			$('#ai_report_markdown').text(md);
			var longText = String(md || '').length > 900;
			setOverviewExpanded(!longText, false);
			$('#ai_overview_toggle').toggle(longText);
			$('.executive_overview_div').toggle(!!String(md||'').trim());
		}

		function recStorageKey(){
			return 'wprev_tripadvisor_ai_rec_done_sample';
		}
		function loadDoneRecs(){
			try { return JSON.parse(localStorage.getItem(recStorageKey()) || '{}') || {}; } catch(e){ return {}; }
		}
		function saveDoneRecs(map){
			try { localStorage.setItem(recStorageKey(), JSON.stringify(map || {})); } catch(e){}
		}

		function initials(name){
			return String(name||'?').split(/\s+/).filter(Boolean).map(function(w){ return w.charAt(0); }).join('').slice(0,2).toUpperCase() || '?';
		}

		function meterWidth(value, max){
			if(!max){ return 8; }
			return Math.max(8, Math.round((Number(value)||0) / max * 100));
		}

		function truncate(s,n){ if(!s) return ''; s=String(s); return s.length>n? s.slice(0,n-1)+'…' : s; }

		function quotesHtml(quotes, uidPrefix){
			quotes = Array.isArray(quotes) ? quotes.filter(Boolean) : [];
			if(!quotes.length){ return ''; }
			var first = truncate(quotes[0], 160);
			var moreHtml = '';
			if(quotes.length > 1){
				moreHtml = '<div id="'+uidPrefix+'" class="ai-quote-more" style="display:none">'+ quotes.slice(1).map(function(q){return '<div class="ai-quote">“'+ aiEscapeHTML(truncate(q, 240)) +'”</div>';}).join('') + '</div>'+
					'<a href="#" class="ai-readmore" data-target="'+uidPrefix+'">Read more</a>';
			}
			return '<div class="ai-quote">“'+ aiEscapeHTML(first) +'”</div>' + moreHtml;
		}

		function renderAIReport(report){
			report = report || {};
			window._aiLastReport = report;
			var uid = 0;
			var m = report.metrics || {};
			var counts = m.counts || {};
			var total = Number(counts.total_reviews || 0) || 0;
			var pos = Number(counts.positive || 0) || 0;
			var neu = Number(counts.neutral || 0) || 0;
			var neg = Number(counts.negative || 0) || 0;
			if(!total && (pos || neu || neg)){ total = pos + neu + neg; }
			var posPct = total ? Math.round((pos / total) * 100) : null;
			var themes = Array.isArray(report.themes) ? report.themes : [];
			var pains = Array.isArray(report.pain_points) ? report.pain_points : [];
			var recs = Array.isArray(report.recommendations) ? report.recommendations : [];
			var personas = Array.isArray(report.personas) ? report.personas : [];
			var faqs = Array.isArray(report.faqs) ? report.faqs : [];
			var strength = themes.slice().sort(function(a,b){ return (Number(b.count)||0) - (Number(a.count)||0); })[0] || null;
			var sevRank = { high: 3, medium: 2, low: 1 };
			var topPain = pains.slice().sort(function(a,b){
				return (sevRank[String(b.severity||'').toLowerCase()]||0) - (sevRank[String(a.severity||'').toLowerCase()]||0);
			})[0] || null;
			var highPains = pains.filter(function(p){ return String(p.severity||'').toLowerCase() === 'high'; });

			var $banner = $('#ai_alert_banner').removeClass('is-warn').empty().hide();
			if(highPains.length){
				$banner.html('High-priority issue' + (highPains.length > 1 ? 's' : '') + ': ' + aiEscapeHTML(highPains[0].issue || 'Customer friction') + (highPains.length > 1 ? ' and ' + (highPains.length - 1) + ' more. Start in the Action Center below.' : '. See the Action Center for the first step.')).show();
			} else if(posPct !== null && posPct < 60 && neg > 0){
				$banner.addClass('is-warn').html('Positive sentiment is ' + posPct + '%. Review the recommendations below before this becomes the story customers tell.').show();
			}

			var kpis = [];
			kpis.push('<div class="ai-kpi"><div class="ai-kpi-label">Avg rating</div><div class="ai-kpi-value">'+ (m.avg_rating != null ? aiEscapeHTML(m.avg_rating) : '—') +'</div><div class="ai-kpi-sub">Across analyzed reviews</div></div>');
			kpis.push('<div class="ai-kpi"><div class="ai-kpi-label">Reviews analyzed</div><div class="ai-kpi-value">'+ total +'</div><div class="ai-kpi-sub">In this report</div></div>');
			var sentClass = posPct === null ? 'is-neutral' : (posPct >= 70 ? 'is-positive' : (posPct < 50 ? 'is-negative' : 'is-neutral'));
			kpis.push('<div class="ai-kpi '+sentClass+'"><div class="ai-kpi-label">Positive share</div><div class="ai-kpi-value">'+ (posPct !== null ? posPct + '%' : '—') +'</div><div class="ai-kpi-sub">'+ pos +' positive · '+ neg +' negative</div></div>');
			if(topPain){
				kpis.push('<div class="ai-kpi is-negative"><div class="ai-kpi-label">Top pain point</div><div class="ai-kpi-value" style="font-size:18px">'+ aiEscapeHTML(topPain.issue || '—') +'</div><div class="ai-kpi-sub">'+ aiEscapeHTML(topPain.severity ? String(topPain.severity).charAt(0).toUpperCase()+String(topPain.severity).slice(1)+' severity' : (strength ? 'Top strength: '+strength.name : 'Needs attention')) +'</div></div>');
			} else if(strength){
				kpis.push('<div class="ai-kpi is-positive"><div class="ai-kpi-label">Top strength</div><div class="ai-kpi-value" style="font-size:18px">'+ aiEscapeHTML(strength.name || '—') +'</div><div class="ai-kpi-sub">Mentioned '+ (strength.count || 0) +' times</div></div>');
			} else {
				kpis.push('<div class="ai-kpi"><div class="ai-kpi-label">Top theme</div><div class="ai-kpi-value">—</div><div class="ai-kpi-sub">No themes in this sample</div></div>');
			}
			$('#ai_hero_kpis').html(kpis.join(''));

			destroyChart('doughnut');
			var $doughnutPanel = $('.ai-hero-doughnut');
			if ((pos || neu || neg) && typeof Chart !== 'undefined'){
				$doughnutPanel.show();
				var dctx = document.getElementById('ai_sentiment_doughnut');
				if(dctx){
					aiCharts.doughnut = new Chart(dctx, {
						type: 'doughnut',
						data: {
							labels: ['Positive', 'Neutral', 'Negative'],
							datasets: [{
								data: [pos, neu, neg],
								backgroundColor: ['#10B981', '#94A3B8', '#EF4444'],
								borderWidth: 0
							}]
						},
						options: {
							responsive: true,
							maintainAspectRatio: false,
							cutoutPercentage: 68,
							legend: { display: false },
							tooltips: { enabled: true }
						}
					});
				}
				$('#ai_doughnut_legend').html(
					'<span><span class="ai-legend-dot" style="background:#10B981"></span>Positive '+pos+'</span>'+
					'<span><span class="ai-legend-dot" style="background:#94A3B8"></span>Neutral '+neu+'</span>'+
					'<span><span class="ai-legend-dot" style="background:#EF4444"></span>Negative '+neg+'</span>'
				);
			} else {
				$doughnutPanel.hide();
			}

			destroyChart('timeline');
			var $timelinePanel = $('.sentiment_over_time_div');
			try{
				if (m && Array.isArray(m.timeline) && m.timeline.length && typeof Chart !== 'undefined'){
					$timelinePanel.show();
					var t = m.timeline;
					var labels = t.map(function(p){ return p.date || p.period || ''; });
					var tpos = t.map(function(p){ return p.positive!=null? Number(p.positive): null; });
					var tneu = t.map(function(p){ return p.neutral!=null? Number(p.neutral): null; });
					var tneg = t.map(function(p){ return p.negative!=null? Number(p.negative): null; });
					var ctx = document.getElementById('ai_sentiment_timeline');
					if (ctx){
						aiCharts.timeline = new Chart(ctx, {
							type: 'line',
							data: { labels: labels, datasets: [
								{ label: 'Positive', data: tpos, borderColor: '#10B981', backgroundColor: 'rgba(16,185,129,.12)', fill: true, lineTension: .25, spanGaps: true },
								{ label: 'Neutral', data: tneu, borderColor: '#94A3B8', backgroundColor: 'rgba(148,163,184,.10)', fill: true, lineTension: .25, spanGaps: true },
								{ label: 'Negative', data: tneg, borderColor: '#EF4444', backgroundColor: 'rgba(239,68,68,.10)', fill: true, lineTension: .25, spanGaps: true }
							] },
							options: {
								responsive: true,
								maintainAspectRatio: false,
								legend: { position: 'bottom' },
								scales: {
									yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }],
									xAxes: [{ ticks: { autoSkip: true, maxRotation: 0 } }]
								}
							}
						});
						$('#ai_sentiment_timeline').off('click').on('click', function(e){
							var points = aiCharts.timeline && aiCharts.timeline.getElementsAtEvent(e);
							if(points && points.length>0){
								var idx = points[0]._index;
								var dateKey = labels[idx];
								if(!dateKey){ return; }
								var url = "#TB_inline?width=auto&height=auto&inlineId=tb_content_popup";
								tb_show(dateKey || 'Reviews', url);
								$("#TB_window").css({ "width":"80%","margin-left": "-40%","height":"80vh","top":"300px" });
								$("#TB_ajaxContent").css({ "width":"auto","height":"auto","max-height":"62vh","overflow":"auto" });
								$("#TB_window").focus();
								$("#review_details").hide();
								$("#review_list").show();
								var data = { action: 'wptripadvisor_ai_reviews_by_date', wpfb_nonce: adminjs_script_vars.wpfb_nonce, report_id: '', date: dateKey };
								$.post(adminjs_script_vars.ajax_url, data).then(function(resp){
									var reviewshtml = '';
									if(resp && resp.success && resp.data && Array.isArray(resp.data.reviews)){
										resp.data.reviews.forEach(function(value){ reviewshtml += getreviewshtml(value); });
									}
									if(!reviewshtml){
										reviewshtml = '<tr><td colspan="6">'+ aiEscapeHTML('Sample reviews for this date are available in the Pro version.') +'</td></tr>';
									}
									$("#review_list_body").html(reviewshtml);
								});
							}
						});
					}
				} else {
					$timelinePanel.hide();
				}
			} catch(e){
				$timelinePanel.hide();
			}
			$('.ai-hero-viz').toggle($doughnutPanel.css('display') !== 'none' || $timelinePanel.css('display') !== 'none');

			$('#ai_summary_panel').empty().hide();

			var themeMax = themes.reduce(function(max, theme){ return Math.max(max, Number(theme.count)||0); }, 0);
			if(themes.length){
				var themeHtml = '<div class="ai-panel-title">Themes customers mention</div>';
				themeHtml += themes.map(function(theme){
					var moreId = 'ai-more-t-'+(uid++);
					return '<div class="ai-meter-row"><div class="ai-meter-head"><span class="ai-meter-name">'+ aiEscapeHTML(theme.name||'') +'</span><span class="ai-meter-count">'+ (theme.count||0) +'</span></div>'+
						'<div class="ai-meter-track"><div class="ai-meter-fill is-positive" style="width:'+ meterWidth(theme.count, themeMax) +'%"></div></div>'+
						quotesHtml(theme.sample_quotes, moreId) + '</div>';
				}).join('');
				$('#ai_themes_panel').html(themeHtml).show();
			} else {
				$('#ai_themes_panel').html('<div class="ai-panel-title">Themes</div><p class="ai-empty">No themes in this report.</p>').show();
			}

			var painMax = pains.reduce(function(max, p){ return Math.max(max, sevRank[String(p.severity||'').toLowerCase()] || 1); }, 0);
			if(pains.length){
				var painHtml = '<div class="ai-panel-title">Pain points to fix</div>';
				painHtml += pains.map(function(p){
					var sev = String(p.severity||'').toLowerCase();
					var moreId = 'ai-more-p-'+(uid++);
					return '<div class="ai-meter-row"><div class="ai-meter-head"><span class="ai-meter-name"><span class="ai-chip ai-chip--'+ sev +'">'+ aiEscapeHTML(p.severity||'issue') +'</span> '+ aiEscapeHTML(p.issue||'') +'</span></div>'+
						'<div class="ai-meter-track"><div class="ai-meter-fill is-'+ (sev || 'high') +'" style="width:'+ meterWidth(sevRank[sev]||1, painMax) +'%"></div></div>'+
						quotesHtml(p.examples, moreId) + '</div>';
				}).join('');
				$('#ai_pain_points_panel').html(painHtml).show();
			} else {
				$('#ai_pain_points_panel').html('<div class="ai-panel-title">Pain points</div><p class="ai-empty">No pain points flagged.</p>').show();
			}

			if(personas.length){
				var personaHtml = '<div class="ai-panel-title" style="margin-bottom:10px">Who is writing these reviews</div><div class="ai-persona-grid">';
				personaHtml += personas.map(function(pe){
					var type = pe.type || pe.label || 'Persona';
					var desc = pe.description || (Array.isArray(pe.traits) ? pe.traits.join(', ') : '');
					return '<div class="ai-persona-card"><div class="ai-persona-avatar">'+ aiEscapeHTML(initials(type)) +'</div><div><div class="ai-persona-name">'+ aiEscapeHTML(type) +'</div><div>'+ aiEscapeHTML(desc) +'</div></div></div>';
				}).join('');
				personaHtml += '</div>';
				$('#ai_personas_panel').html(personaHtml).show();
			} else {
				$('#ai_personas_panel').empty().hide();
			}

			if(report.swot){
				function swotTile(key, title, items){
					items = Array.isArray(items) ? items : [];
					var lis = items.length ? items.map(function(x){ return '<li>'+ aiEscapeHTML(x) +'</li>'; }).join('') : '<li class="ai-empty">—</li>';
					return '<div class="ai-swot-tile is-'+key+'"><h3>'+ title +'</h3><ul>'+ lis +'</ul></div>';
				}
				$('#ai_swot_panel').html('<div class="ai-panel-title" style="margin-bottom:10px">SWOT</div><div class="ai-swot-grid">'+
					swotTile('strengths', 'Strengths', report.swot.strengths)+
					swotTile('weaknesses', 'Weaknesses', report.swot.weaknesses)+
					swotTile('opportunities', 'Opportunities', report.swot.opportunities)+
					swotTile('threats', 'Threats', report.swot.threats)+
				'</div>').show();
			} else {
				$('#ai_swot_panel').empty().hide();
			}

			if(faqs.length){
				var faqHtml = '<div class="ai-panel-title">Questions customers already answered</div>';
				faqHtml += faqs.map(function(f, i){
					return '<div class="ai-faq-item'+(i===0?' is-open':'')+'"><button type="button" class="ai-faq-q">'+ aiEscapeHTML(f.question||'Question') +'</button><div class="ai-faq-a">'+ aiEscapeHTML(f.answer||'') +'</div></div>';
				}).join('');
				$('#ai_faqs_panel').html(faqHtml).show();
			} else {
				$('#ai_faqs_panel').html('<div class="ai-panel-title">FAQs</div><p class="ai-empty">No FAQs in this report.</p>').show();
			}

			if(report.notes){
				$('#ai_notes_panel').html('<div class="ai-panel-title">Notes</div><div>'+ aiEscapeHTML(report.notes) +'</div>').show();
			} else {
				$('#ai_notes_panel').empty().hide();
			}

			var doneMap = loadDoneRecs();
			if(recs.length){
				var recHtml = '<div class="ai-panel-title">Recommendations</div><ul class="ai-rec-list">';
				recHtml += recs.map(function(r, idx){
					var title = (typeof r === 'string') ? r : (r.title || r.text || '');
					var rationale = (typeof r === 'object' && r) ? (r.rationale || '') : '';
					var impact = (typeof r === 'object' && r && r.impact) ? String(r.impact) : '';
					var effort = (typeof r === 'object' && r && r.effort) ? String(r.effort) : '';
					var key = 'rec-'+idx+'-'+ String(title).slice(0,40);
					var checked = !!doneMap[key];
					var tags = '';
					if(impact){ tags += '<span class="ai-chip ai-chip--impact-'+ impact.toLowerCase() +'">Impact: '+ aiEscapeHTML(impact) +'</span>'; }
					if(effort){ tags += '<span class="ai-chip ai-chip--effort">Effort: '+ aiEscapeHTML(effort) +'</span>'; }
					return '<li class="ai-rec-item'+(checked?' is-done':'')+'"><div class="ai-rec-head">'+
						'<input type="checkbox" class="ai-rec-check" data-key="'+ aiEscapeHTML(key) +'" '+(checked?'checked':'')+'>'+
						'<div><div class="ai-rec-title">'+ aiEscapeHTML(title) +'</div>'+
						(tags ? '<div class="ai-rec-tags">'+ tags +'</div>' : '')+
						(rationale ? '<div class="ai-rec-note">'+ aiEscapeHTML(rationale) +'</div>' : '')+
						'</div></div></li>';
				}).join('');
				recHtml += '</ul>';
				$('#ai_recommendations').html(recHtml).show();
			} else {
				$('#ai_recommendations').html('<div class="ai-panel-title">Recommendations</div><p class="ai-empty">No recommendations in this report.</p>').show();
			}

			var growth = report.review_growth;
			if(growth && (growth.summary || (Array.isArray(growth.tactics) && growth.tactics.length))){
				var gHtml = '<div class="ai-panel-title">How to grow reviews</div>';
				if(growth.summary){ gHtml += '<p class="ai-summary-lead">'+ aiEscapeHTML(growth.summary) +'</p>'; }
				if(Array.isArray(growth.tactics) && growth.tactics.length){
					gHtml += '<ul class="ai-growth-list">';
					gHtml += growth.tactics.map(function(tactic, i){
						var steps = Array.isArray(tactic.steps) ? tactic.steps : [];
						return '<li class="ai-growth-item'+(i===0?' is-open':'')+'">'+
							'<button type="button" class="ai-growth-toggle">'+ aiEscapeHTML(tactic.title||'Tactic') +'</button>'+
							'<div class="ai-growth-body">'+
							(tactic.rationale ? '<div class="ai-rec-note">'+ aiEscapeHTML(tactic.rationale) +'</div>' : '')+
							(steps.length ? '<ol class="ai-steps">'+ steps.map(function(s){ return '<li>'+ aiEscapeHTML(s) +'</li>'; }).join('') +'</ol>' : '')+
							'</div></li>';
					}).join('');
					gHtml += '</ul>';
				}
				$('#ai_review_growth').html(gHtml).show();
			} else {
				$('#ai_review_growth').html('<div class="ai-panel-title">How to grow reviews</div><p class="ai-empty">No growth tactics in this report.</p>').show();
			}

			var hasActions = recs.length || (growth && (growth.summary || (growth.tactics && growth.tactics.length)));
			$('#ai_action_center').toggle(!!hasActions);
			$('#tab-themes').toggle(!!(themes.length || pains.length));
			$('#tab-audience').toggle(!!(personas.length || report.swot));
			$('#tab-faqs').toggle(!!(faqs.length || report.notes));
		}

		function wprev_prourldecode(url) {
		  return decodeURIComponent(String(url||'').replace(/\+/g, ' '));
		}
		function getreviewshtml(value){
			var userpic = '';
			if(value.userpiclocal && value.userpiclocal!==''){
				userpic = '<img style="width: 50px;" src="'+aiEscapeHTML(value.userpiclocal)+'" alt="">';
			} else {
				userpic = '<img style="width: 50px;" src="'+aiEscapeHTML(value.userpic||'')+'" alt="">';
			}
			var fromurllink = wprev_prourldecode(value.from_url||'');
			return '<tr><th scope="col">'+userpic+'<br>'+aiEscapeHTML(value.reviewer_name||'')+'</th><th scope="col"><b>'+aiEscapeHTML(value.rating||'')+'</b></th><th scope="col">'+aiEscapeHTML(value.review_text||'')+'</th><th scope="col">'+aiEscapeHTML(value.created_time||'')+'</th><th scope="col">'+aiEscapeHTML(value.review_length||'')+'</th><th scope="col"><a href="'+aiEscapeHTML(fromurllink)+'" target="_blank">'+aiEscapeHTML(value.type||'')+'</a></th></tr>';
		}

		$('#wprevpro_ai_analysis_page').on('click','.ai-readmore', function(e){
			e.preventDefault();
			var id = $(this).data('target');
			var $t = $('#'+id);
			var show = !$t.is(':visible');
			$t.toggle(show);
			$(this).text(show ? 'Read less' : 'Read more');
		});

		$('#ai_overview_toggle').on('click', function(){
			var collapsed = $('#ai_report_markdown').hasClass('is-collapsed');
			setOverviewExpanded(collapsed, true);
		});

		$('#wprevpro_ai_analysis_page').on('change', '.ai-rec-check', function(){
			var map = loadDoneRecs();
			var key = $(this).data('key');
			if($(this).is(':checked')){ map[key] = 1; } else { delete map[key]; }
			saveDoneRecs(map);
			$(this).closest('.ai-rec-item').toggleClass('is-done', $(this).is(':checked'));
		});

		$('#wprevpro_ai_analysis_page').on('click', '.ai-faq-q', function(){
			$(this).closest('.ai-faq-item').toggleClass('is-open');
		});

		$('#wprevpro_ai_analysis_page').on('click', '.ai-growth-toggle', function(){
			$(this).closest('.ai-growth-item').toggleClass('is-open');
		});

		function setEmptyState(isEmpty){
			if(isEmpty){
				$('#ai_dashboard').hide();
			} else {
				$('#ai_dashboard').show();
			}
		}
	});
})(jQuery);
