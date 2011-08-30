package com.networkmanagerapp;

import android.app.Activity;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.webkit.WebView;

public class WeathermapViewer extends Activity {
	private static WeathermapViewer instance; //represent the current instance of the class
	WebView weathermapView;
	
	/**
	 * get the current instance of the class, for use with the menu.
	 * @return the current instance
	 */
	public static WeathermapViewer getInstance() {
		return instance;
	}

	/**
	 * Set the current instance of the class
	 * @param instance represents the current instance
	 */
	public static void setInstance(WeathermapViewer instance) {
		WeathermapViewer.instance = instance;
	}

	/**
	 * Called by Android whenever the activity is created
	 * uses res/layout/weathermap.xml
	 */
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		instance = this;
		setContentView(R.layout.weathermap);
		weathermapView = (WebView)findViewById(R.id.WeathermapView);
		getData();
	}
	
	/**
	 * reload the page in the webview
	 */
	protected void getData(){
		String ip = PreferenceManager.getDefaultSharedPreferences(this).getString("ip_preference", "192.168.1.1");
		if(ip.startsWith("192.168")){
			ip = ip.substring(0, ip.lastIndexOf('.'));
			ip = ip + ".225";
		}
		weathermapView.loadUrl("http://" + ip + ":1085/weathermap/index.php?config=weathermap.conf");
	}
}
